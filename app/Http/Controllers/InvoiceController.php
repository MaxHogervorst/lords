<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\InvoiceRepository;
use App\Repositories\MemberRepository;
use App\Repositories\ProductRepository;
use App\Services\InvoiceCalculationService;
use App\Services\InvoiceExportService;
use App\Services\SepaGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceCalculationService $calculationService,
        private readonly SepaGenerationService $sepaService,
        private readonly InvoiceExportService $exportService,
        private readonly InvoiceRepository $invoiceRepository,
        private readonly MemberRepository $memberRepository,
        private readonly ProductRepository $productRepository
    ) {
    }

    /**
     * Get member from session by ID.
     */
    private function getSessionMember(): ?object
    {
        $memberId = session('member_id');

        return $memberId ? $this->memberRepository->find($memberId) : null;
    }

    /**
     * Get invoice group from session by ID.
     */
    private function getSessionInvoiceGroup(): ?object
    {
        $invoiceGroupId = session('personal_invoice_group_id');

        return $invoiceGroupId ? $this->invoiceRepository->find($invoiceGroupId) : null;
    }

    public function getIndex(): View
    {
        $currentmonth = $this->invoiceRepository->getCurrentMonth();
        $products = $this->productRepository->getAllAsArrayIdAsKey();

        // Eager load orders and groups filtered by current invoice group
        $members = $this->memberRepository->all(
            ['*'],
            [
                'orders' => function ($query) use ($currentmonth) {
                    $query->where('invoice_group_id', $currentmonth->id);
                },
                'orders.product',
                'groups' => function ($query) use ($currentmonth) {
                    $query->where('invoice_group_id', $currentmonth->id);
                },
                'groups.orders' => function ($query) use ($currentmonth) {
                    $query->where('invoice_group_id', $currentmonth->id);
                },
                'groups.orders.product',
                'groups.members',
                'invoice_lines.productprice.product'
            ]
        );

        return view('invoice.index')
            ->with('invoicegroups', $this->invoiceRepository->getAllOrdered('desc'))
            ->with('currentmonth', $currentmonth)
            ->with('members', $members)
            ->with('products', $products);
    }

    public function getPerPerson(): View
    {
        $m = null;
        $sessionMember = $this->getSessionMember();

        if (! is_null($sessionMember)) {
            $m = $this->memberRepository->findWithInvoiceLinesByMemberId($sessionMember->id);
        }

        $products = $this->productRepository->getAllAsArrayIdAsKey();
        $currentmonth = $this->getSessionInvoiceGroup() ?? $this->invoiceRepository->getCurrentMonth();

        return view('invoice.person')
            ->with('invoicegroups', $this->invoiceRepository->getAllOrdered('desc'))
            ->with('currentmonth', $currentmonth)
            ->with('m', $m)
            ->with('products', $products);
    }

    public function getPdf(): View
    {
        $currentmonth = $this->invoiceRepository->getCurrentMonth();

        return view('invoice.pdf')
            ->with('currentmonth', $currentmonth)
            ->with('members', $this->memberRepository->all(
                ['*'],
                ['orders.product', 'groups.orders.product', 'groups.members', 'invoice_lines.productprice.product']
            ));
    }

    public function getExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return $this->exportService->exportToExcel();
    }

    public function getSepa(): View
    {
        $membersWithoutBankInfo = $this->sepaService->getMembersWithoutBankInfo();
        $members = $this->sepaService->collectMembersForSepa();
        $batchData = $this->sepaService->createBatches($members);
        $batchLinks = $this->sepaService->saveBatchesToDisk($batchData);

        return view('invoice.sepa')
            ->with('total', count($batchLinks))
            ->with('batchlink', $batchLinks)
            ->with('memberswithtohightransaction', $batchData['failedMembers'])
            ->with('memberswithoutbankinfo', $membersWithoutBankInfo);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function postStoreinvoicegroup(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['invoiceMonth' => 'required']);

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        }

        $invoicegroup = $this->invoiceRepository->createAndSetActive($request->get('invoiceMonth'));

        if (! $invoicegroup->exists) {
            return response()->json(['errors' => 'Could not be added to the database']);
        }

        Cache::forget('invoice_group');

        return response()->json(['success' => true, 'id' => $invoicegroup->id, 'name' => $invoicegroup->name]);
    }

    public function postSetPersonalInvoiceGroup(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['invoiceGroup' => 'required']);

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        } else {
            $invoicegroup = $this->invoiceRepository->find((int) $request->get('invoiceGroup'));

            if (! is_null($invoicegroup)) {
                session(['personal_invoice_group_id' => $invoicegroup->id]);

                return response()->json(['success' => true]);
            } else {
                return response()->json(['errors' => 'Could not find month']);
            }
        }
    }

    public function postSetPerson(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['name' => 'required', 'iban' => 'required']);

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        } else {
            $member = $this->memberRepository->findByLastnameAndIban(
                $request->get('name'),
                $request->get('iban')
            );
            if (! is_null($member)) {
                session(['member_id' => $member->id]);

                return response()->json(['success' => true]);
            } else {
                return response()->json(['errors' => 'Could not find member']);
            }
        }
    }

    public function postSelectinvoicegroup(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['invoiceGroup' => 'required']);

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        }

        $invoicegroup = $this->invoiceRepository->find((int) $request->get('invoiceGroup'));
        $this->invoiceRepository->setAsActive($invoicegroup);

        // Verify the operation was successful
        $currentMonth = $this->invoiceRepository->getCurrentMonth();
        if (is_null($currentMonth)) {
            return response()->json(['errors' => 'Could not be added to the database']);
        }

        Cache::forget('invoice_group');

        return response()->json(['success' => true]);
    }

}
