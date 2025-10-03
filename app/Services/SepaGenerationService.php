<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SepaSequenceType;
use App\Repositories\MemberRepository;
use DateTime;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Illuminate\Support\Facades\Storage;
use Settings;

class SepaGenerationService
{
    private string $currentPaymentInfo = '';

    public function __construct(
        private readonly InvoiceCalculationService $calculationService,
        private readonly MemberRepository $memberRepository
    ) {
    }

    /**
     * Generate SEPA files and return batch information.
     */
    public function generateSepaFiles(): array
    {
        $members = $this->collectMembersForSepa();
        $batches = $this->createBatches($members);
        $batchLinks = $this->saveBatchesToDisk($batches);

        return [
            'totalBatches' => count($batchLinks),
            'batchLinks' => $batchLinks,
        ];
    }

    /**
     * Collect and organize members by SEPA sequence type.
     */
    public function collectMembersForSepa(): array
    {
        $members = ['RCUR' => [], 'FRST' => []];

        // Recurring members
        $memberRcur = $this->memberRepository->getMembersWithRcur(
            ['orders.product', 'groups.orders.product', 'invoice_lines.productprice.product']
        );

        foreach ($memberRcur as $member) {
            $info = $this->calculationService->generateMemberInfo($member);
            if ($info !== null) {
                $members['RCUR'][] = $info;
            }
        }

        // First-time members
        $memberFrst = $this->memberRepository->getMembersWithFrst(
            ['orders.product', 'groups.orders.product', 'invoice_lines.productprice.product']
        );

        foreach ($memberFrst as $member) {
            $info = $this->calculationService->generateMemberInfo($member);
            if ($info !== null) {
                $members['FRST'][] = $info;
            }
        }

        return $members;
    }

    /**
     * Create SEPA batches from member data.
     */
    public function createBatches(array $members): array
    {
        $batches = ['RCUR' => [], 'FRST' => []];
        $batchFailedMembers = [];

        $maxMoneyPerBatch = Settings::get('creditorMaxMoneyPerBatch', 999999);
        $maxTransactionsPerBatch = Settings::get('creditorMaxTransactionsPerBatch', 1000);
        $maxMoneyPerTransaction = Settings::get('creditorMaxMoneyPerTransaction', 100000);

        // Process RCUR batches
        if (!empty($members['RCUR'])) {
            $batches['RCUR'] = $this->processMembersIntoBatches(
                $members['RCUR'],
                SepaSequenceType::RECURRING,
                $maxMoneyPerBatch,
                $maxTransactionsPerBatch,
                $maxMoneyPerTransaction,
                $batchFailedMembers
            );
        }

        // Process FRST batches
        if (!empty($members['FRST'])) {
            $batches['FRST'] = $this->processMembersIntoBatches(
                $members['FRST'],
                SepaSequenceType::FIRST,
                $maxMoneyPerBatch,
                $maxTransactionsPerBatch,
                $maxMoneyPerTransaction,
                $batchFailedMembers
            );
        }

        return [
            'batches' => $batches,
            'failedMembers' => $batchFailedMembers,
        ];
    }

    /**
     * Process members into batches based on constraints.
     */
    private function processMembersIntoBatches(
        array $members,
        SepaSequenceType $seqType,
        float $maxMoneyPerBatch,
        int $maxTransactionsPerBatch,
        float $maxMoneyPerTransaction,
        array &$batchFailedMembers
    ): array {
        $batches = [];
        $currentBatch = $this->createBatch($seqType);
        $transactions = 0;
        $batchTotalMoney = 0.0;

        foreach ($members as $memberInfo) {
            // Check if we need a new batch
            if ($batchTotalMoney + $memberInfo['amount'] > $maxMoneyPerBatch
                || $transactions == $maxTransactionsPerBatch) {
                $batches[] = $currentBatch;
                $currentBatch = $this->createBatch($seqType);
                $transactions = 0;
                $batchTotalMoney = 0.0;
            }

            // Check if transaction exceeds limit
            if ($memberInfo['amount'] > $maxMoneyPerTransaction) {
                $batchFailedMembers[] = $memberInfo;
            } else {
                $this->addTransferToBatch($currentBatch, $memberInfo);
                $batchTotalMoney += $memberInfo['amount'];
                $transactions++;
            }
        }

        if ($transactions > 0) {
            $batches[] = $currentBatch;
        }

        return $batches;
    }

    /**
     * Create a new SEPA batch.
     */
    private function createBatch(SepaSequenceType $seqType): mixed
    {
        $prefix = Settings::get('filePrefix', 'GSRC');
        $timestamp = date('Y-m-d H:i:s');
        $this->currentPaymentInfo = $prefix . $timestamp;

        $batch = TransferFileFacadeFactory::createDirectDebit(
            $prefix . $timestamp,
            'me',
            Settings::get('creditorPain', 'pain.008.001.02')
        );

        $daysOffset = Settings::get('ReqdColltnDt', 5);
        $dueDate = new DateTime(date('Y-m-d', strtotime("now +{$daysOffset} weekdays")));

        $batch->addPaymentInfo($this->currentPaymentInfo, [
            'id' => $prefix . $timestamp,
            'creditorName' => Settings::get('creditorName'),
            'creditorAccountIBAN' => Settings::get('creditorAccountIBAN'),
            'creditorAgentBIC' => Settings::get('creditorAgentBIC'),
            'seqType' => $seqType->value,
            'creditorId' => Settings::get('creditorId'),
            'dueDate' => $dueDate,
        ]);

        return $batch;
    }

    /**
     * Add a transfer to a batch.
     */
    private function addTransferToBatch(mixed $batch, array $memberInfo): void
    {
        $mandateSignDate = Settings::get('mandateSignDate', '2014-01-01');
        $remittancePrefix = Settings::get('remittancePrefix', 'Contributie');

        $batch->addTransfer($this->currentPaymentInfo, [
            'amount' => (int) round($memberInfo['amount'] * 100),
            'debtorIban' => $memberInfo['iban'],
            'debtorBic' => $memberInfo['bic'],
            'debtorName' => $memberInfo['name'],
            'debtorMandate' => $memberInfo['mandate'],
            'debtorMandateSignDate' => $mandateSignDate,
            'remittanceInformation' => $remittancePrefix . ' ' . date('Y-m'),
        ]);
    }

    /**
     * Save batches to disk and return file links.
     */
    public function saveBatchesToDisk(array $batchData): array
    {
        $filePrefix = Settings::get('filePrefix', 'GSRC');
        $batchLinks = [];

        // Save RCUR batches
        $i = 0;
        foreach ($batchData['batches']['RCUR'] ?? [] as $batch) {
            $i++;
            $filename = "{$filePrefix} RCUR {$i} " . date('Y-m-d') . '.xml';
            Storage::disk('sepa')->put($filename, $batch->asXML());
            $batchLinks[] = $filename;
        }

        // Save FRST batches
        $i = 0;
        foreach ($batchData['batches']['FRST'] ?? [] as $batch) {
            $i++;
            $filename = "{$filePrefix} FRST {$i} " . date('Y-m-d') . '.xml';
            Storage::disk('sepa')->put($filename, $batch->asXML());
            $batchLinks[] = $filename;
        }

        return $batchLinks;
    }

    /**
     * Get members without bank information.
     */
    public function getMembersWithoutBankInfo()
    {
        return $this->memberRepository->getMembersWithoutBankInfo();
    }
}
