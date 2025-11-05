<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use Tests\TestCase;

class SepaXmlGenerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Configure SEPA settings
        \Settings::set('creditorName', 'Test Creditor Organization');
        \Settings::set('creditorAccountIBAN', 'NL91ABNA0417164300');
        \Settings::set('creditorAgentBIC', 'ABNANL2A');
        \Settings::set('creditorId', 'NL00ZZZ000000000000');
        \Settings::set('creditorPain', 'pain.008.001.02');
        \Settings::set('creditorMaxMoneyPerBatch', 999999);
        \Settings::set('creditorMaxMoneyPerTransaction', 100000);
        \Settings::set('creditorMaxTransactionsPerBatch', 1000);
        \Settings::set('ReqdColltnDt', 5);
        \Settings::set('mandateSignDate', '2014-01-01');
        \Settings::set('remittancePrefix', 'Contributie');
        \Settings::set('filePrefix', 'TEST');
        \Settings::save();

        // Ensure SEPA directory exists and is empty
        $sepaDir = storage_path('SEPA');
        if (!file_exists($sepaDir)) {
            mkdir($sepaDir, 0755, true);
        }
        // Clean up any existing test files
        array_map('unlink', glob("$sepaDir/TEST*.xml"));
    }

    protected function tearDown(): void
    {
        // Clean up test files
        $sepaDir = storage_path('SEPA');
        if (file_exists($sepaDir)) {
            array_map('unlink', glob("$sepaDir/TEST*.xml"));
        }

        parent::tearDown();
    }

    /**
     * Test SEPA XML file is generated with correct structure
     */
    public function test_sepa_xml_has_valid_structure(): void
    {
        $user = \App\Models\User::factory()->create(['is_admin' => true]);

        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);

        // Create member with RCUR mandate
        $member = Member::factory()->create([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'iban' => 'NL20INGB0001234567',
            'bic' => 'INGBNL2A',
            'had_collection' => true,
        ]);

        $product = Product::factory()->create(['price' => 10.50]);
        Product::toArrayIdAsKey();

        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 2,
        ]);

        $this->actingAs($user)->get('/invoice/sepa');

        // Check that file was created
        $sepaDir = storage_path('SEPA');
        $files = glob("$sepaDir/TEST*.xml");
        $this->assertNotEmpty($files, 'SEPA XML file should be generated');

        // Get the XML content
        $xmlContent = file_get_contents($files[0]);
        $xml = simplexml_load_string($xmlContent);

        // Validate root structure
        $this->assertNotFalse($xml, 'XML should be valid');
        $this->assertEquals('Document', $xml->getName());

        // Register namespaces
        $xml->registerXPathNamespace('pain', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');

        // Validate CstmrDrctDbtInitn exists
        $cstmrDrctDbtInitn = $xml->xpath('//pain:CstmrDrctDbtInitn');
        $this->assertNotEmpty($cstmrDrctDbtInitn, 'CstmrDrctDbtInitn should exist');

        // Validate GrpHdr (Group Header)
        $grpHdr = $xml->xpath('//pain:CstmrDrctDbtInitn/pain:GrpHdr');
        $this->assertNotEmpty($grpHdr, 'Group Header should exist');
    }

    /**
     * Test SEPA XML contains correct creditor information
     */
    public function test_sepa_xml_contains_correct_creditor_info(): void
    {
        $user = \App\Models\User::factory()->create(['is_admin' => true]);

        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);

        $member = Member::factory()->create([
            'iban' => 'NL20INGB0001234567',
            'bic' => 'INGBNL2A',
            'had_collection' => true,
        ]);

        $product = Product::factory()->create(['price' => 5.00]);
        Product::toArrayIdAsKey();

        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 1,
        ]);

        $this->actingAs($user)->get('/invoice/sepa');

        $sepaDir = storage_path('SEPA');
        $files = glob("$sepaDir/TEST*.xml");
        $xmlContent = file_get_contents($files[0]);
        $xml = simplexml_load_string($xmlContent);
        $xml->registerXPathNamespace('pain', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');

        // Check creditor name
        $creditorName = $xml->xpath('//pain:Cdtr/pain:Nm');
        $this->assertNotEmpty($creditorName);
        $this->assertEquals('Test Creditor Organization', (string) $creditorName[0]);

        // Check creditor IBAN
        $creditorIban = $xml->xpath('//pain:CdtrAcct/pain:Id/pain:IBAN');
        $this->assertNotEmpty($creditorIban);
        $this->assertEquals('NL91ABNA0417164300', (string) $creditorIban[0]);

        // Check creditor BIC
        $creditorBic = $xml->xpath('//pain:CdtrAgt/pain:FinInstnId/pain:BIC');
        $this->assertNotEmpty($creditorBic);
        $this->assertEquals('ABNANL2A', (string) $creditorBic[0]);

        // Check creditor ID
        $creditorId = $xml->xpath('//pain:CdtrSchmeId/pain:Id/pain:PrvtId/pain:Othr/pain:Id');
        $this->assertNotEmpty($creditorId);
        $this->assertEquals('NL00ZZZ000000000000', (string) $creditorId[0]);
    }

    /**
     * Test SEPA XML contains correct debtor information
     */
    public function test_sepa_xml_contains_correct_debtor_info(): void
    {
        $user = \App\Models\User::factory()->create(['is_admin' => true]);

        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);

        $member = Member::factory()->create([
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'iban' => 'NL02RABO0123456789',
            'bic' => 'RABONL2U',
            'had_collection' => true,
        ]);

        $product = Product::factory()->create(['price' => 15.75]);
        Product::toArrayIdAsKey();

        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 1,
        ]);

        $this->actingAs($user)->get('/invoice/sepa');

        $sepaDir = storage_path('SEPA');
        $files = glob("$sepaDir/TEST*.xml");
        $xmlContent = file_get_contents($files[0]);
        $xml = simplexml_load_string($xmlContent);
        $xml->registerXPathNamespace('pain', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');

        // Check debtor name
        $debtorName = $xml->xpath('//pain:Dbtr/pain:Nm');
        $this->assertNotEmpty($debtorName);
        $this->assertEquals('Jane Smith', (string) $debtorName[0]);

        // Check debtor IBAN
        $debtorIban = $xml->xpath('//pain:DbtrAcct/pain:Id/pain:IBAN');
        $this->assertNotEmpty($debtorIban);
        $this->assertEquals('NL02RABO0123456789', (string) $debtorIban[0]);

        // Check debtor BIC
        $debtorBic = $xml->xpath('//pain:DbtrAgt/pain:FinInstnId/pain:BIC');
        $this->assertNotEmpty($debtorBic);
        $this->assertEquals('RABONL2U', (string) $debtorBic[0]);
    }

    /**
     * Test SEPA XML contains correct transaction amounts
     */
    public function test_sepa_xml_contains_correct_amounts(): void
    {
        $user = \App\Models\User::factory()->create(['is_admin' => true]);

        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);

        $member = Member::factory()->create([
            'iban' => 'NL20INGB0001234567',
            'bic' => 'INGBNL2A',
            'had_collection' => true,
        ]);

        $product = Product::factory()->create(['price' => 7.50]);
        Product::toArrayIdAsKey();

        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 3, // 3 x 7.50 = 22.50
        ]);

        $this->actingAs($user)->get('/invoice/sepa');

        $sepaDir = storage_path('SEPA');
        $files = glob("$sepaDir/TEST*.xml");
        $xmlContent = file_get_contents($files[0]);
        $xml = simplexml_load_string($xmlContent);
        $xml->registerXPathNamespace('pain', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');

        // Check transaction amount
        $transactionAmount = $xml->xpath('//pain:InstdAmt');
        $this->assertNotEmpty($transactionAmount);
        $this->assertEquals('22.50', (string) $transactionAmount[0]);

        // Check control sum in payment info
        $ctrlSum = $xml->xpath('//pain:PmtInf/pain:CtrlSum');
        $this->assertNotEmpty($ctrlSum);
        $this->assertEquals('22.50', (string) $ctrlSum[0]);
    }

    /**
     * Test SEPA XML uses correct sequence type for RCUR
     */
    public function test_sepa_xml_uses_rcur_sequence_type(): void
    {
        $user = \App\Models\User::factory()->create(['is_admin' => true]);

        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);

        $member = Member::factory()->create([
            'iban' => 'NL20INGB0001234567',
            'bic' => 'INGBNL2A',
            'had_collection' => true, // RCUR
        ]);

        $product = Product::factory()->create(['price' => 5.00]);
        Product::toArrayIdAsKey();

        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 1,
        ]);

        $this->actingAs($user)->get('/invoice/sepa');

        $sepaDir = storage_path('SEPA');
        $files = glob("$sepaDir/TEST*.xml");
        $this->assertStringContainsString('RCUR', $files[0], 'Filename should contain RCUR');

        $xmlContent = file_get_contents($files[0]);
        $xml = simplexml_load_string($xmlContent);
        $xml->registerXPathNamespace('pain', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');

        // Check sequence type
        $seqTp = $xml->xpath('//pain:PmtTpInf/pain:SeqTp');
        $this->assertNotEmpty($seqTp);
        $this->assertEquals('RCUR', (string) $seqTp[0]);
    }

    /**
     * Test SEPA XML uses correct sequence type for FRST
     */
    public function test_sepa_xml_uses_frst_sequence_type(): void
    {
        $user = \App\Models\User::factory()->create(['is_admin' => true]);

        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);

        $member = Member::factory()->create([
            'iban' => 'NL20INGB0001234567',
            'bic' => 'INGBNL2A',
            'had_collection' => false, // FRST
        ]);

        $product = Product::factory()->create(['price' => 5.00]);
        Product::toArrayIdAsKey();

        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 1,
        ]);

        $this->actingAs($user)->get('/invoice/sepa');

        $sepaDir = storage_path('SEPA');
        $files = glob("$sepaDir/TEST*.xml");
        $this->assertStringContainsString('FRST', $files[0], 'Filename should contain FRST');

        $xmlContent = file_get_contents($files[0]);
        $xml = simplexml_load_string($xmlContent);
        $xml->registerXPathNamespace('pain', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');

        // Check sequence type
        $seqTp = $xml->xpath('//pain:PmtTpInf/pain:SeqTp');
        $this->assertNotEmpty($seqTp);
        $this->assertEquals('FRST', (string) $seqTp[0]);
    }

    /**
     * Test SEPA XML contains correct due date
     */
    public function test_sepa_xml_contains_correct_due_date(): void
    {
        $user = \App\Models\User::factory()->create(['is_admin' => true]);

        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);

        $member = Member::factory()->create([
            'iban' => 'NL20INGB0001234567',
            'bic' => 'INGBNL2A',
            'had_collection' => true,
        ]);

        $product = Product::factory()->create(['price' => 5.00]);
        Product::toArrayIdAsKey();

        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 1,
        ]);

        $this->actingAs($user)->get('/invoice/sepa');

        $sepaDir = storage_path('SEPA');
        $files = glob("$sepaDir/TEST*.xml");
        $xmlContent = file_get_contents($files[0]);
        $xml = simplexml_load_string($xmlContent);
        $xml->registerXPathNamespace('pain', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');

        // Check due date exists and is in future
        $reqdColltnDt = $xml->xpath('//pain:ReqdColltnDt');
        $this->assertNotEmpty($reqdColltnDt);

        $dueDate = new \DateTime((string) $reqdColltnDt[0]);
        $today = new \DateTime('today');

        $this->assertGreaterThanOrEqual($today, $dueDate, 'Due date should be today or in the future');
    }

    /**
     * Test SEPA XML excludes members without bank info
     */
    public function test_sepa_xml_excludes_members_without_bank_info(): void
    {
        $user = \App\Models\User::factory()->create(['is_admin' => true]);

        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);

        // Member with bank info
        $memberWithBank = Member::factory()->create([
            'firstname' => 'John',
            'lastname' => 'WithBank',
            'iban' => 'NL20INGB0001234567',
            'bic' => 'INGBNL2A',
            'had_collection' => true,
        ]);

        // Member without bank info
        $memberWithoutBank = Member::factory()->create([
            'firstname' => 'Jane',
            'lastname' => 'NoBank',
            'iban' => null,
            'bic' => null,
        ]);

        $product = Product::factory()->create(['price' => 5.00]);
        Product::toArrayIdAsKey();

        Order::factory()->create([
            'ownerable_id' => $memberWithBank->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 1,
        ]);

        Order::factory()->create([
            'ownerable_id' => $memberWithoutBank->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 1,
        ]);

        $this->actingAs($user)->get('/invoice/sepa');

        $sepaDir = storage_path('SEPA');
        $files = glob("$sepaDir/TEST*.xml");
        $xmlContent = file_get_contents($files[0]);
        $xml = simplexml_load_string($xmlContent);
        $xml->registerXPathNamespace('pain', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');

        // Check number of transactions
        $transactions = $xml->xpath('//pain:DrctDbtTxInf');
        $this->assertCount(1, $transactions, 'Should only have 1 transaction (member without bank info excluded)');

        // Check that the transaction is for the member with bank info
        $debtorName = $xml->xpath('//pain:Dbtr/pain:Nm');
        $this->assertEquals('John WithBank', (string) $debtorName[0]);
    }

    /**
     * Test SEPA XML contains correct remittance information
     */
    public function test_sepa_xml_contains_remittance_information(): void
    {
        $user = \App\Models\User::factory()->create(['is_admin' => true]);

        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);

        $member = Member::factory()->create([
            'iban' => 'NL20INGB0001234567',
            'bic' => 'INGBNL2A',
            'had_collection' => true,
        ]);

        $product = Product::factory()->create(['price' => 5.00]);
        Product::toArrayIdAsKey();

        Order::factory()->create([
            'ownerable_id' => $member->id,
            'ownerable_type' => 'App\\Models\\Member',
            'product_id' => $product->id,
            'invoice_group_id' => $invoiceGroup->id,
            'amount' => 1,
        ]);

        $this->actingAs($user)->get('/invoice/sepa');

        $sepaDir = storage_path('SEPA');
        $files = glob("$sepaDir/TEST*.xml");
        $xmlContent = file_get_contents($files[0]);
        $xml = simplexml_load_string($xmlContent);
        $xml->registerXPathNamespace('pain', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');

        // Check remittance information
        $rmtInf = $xml->xpath('//pain:RmtInf/pain:Ustrd');
        $this->assertNotEmpty($rmtInf);
        $this->assertStringContainsString('Contributie', (string) $rmtInf[0]);
        $this->assertStringContainsString(date('Y-m'), (string) $rmtInf[0]);
    }

    /**
     * Test multiple members create multiple transactions in XML
     */
    public function test_sepa_xml_with_multiple_members(): void
    {
        $user = \App\Models\User::factory()->create(['is_admin' => true]);

        $invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);

        $product = Product::factory()->create(['price' => 5.00]);
        Product::toArrayIdAsKey();

        // Create 3 members with orders
        for ($i = 1; $i <= 3; $i++) {
            $member = Member::factory()->create([
                'firstname' => "Member{$i}",
                'iban' => sprintf('NL%02dINGB000123456%d', 10 + $i, $i),
                'bic' => 'INGBNL2A',
                'had_collection' => true,
            ]);

            Order::factory()->create([
                'ownerable_id' => $member->id,
                'ownerable_type' => 'App\\Models\\Member',
                'product_id' => $product->id,
                'invoice_group_id' => $invoiceGroup->id,
                'amount' => 1,
            ]);
        }

        $this->actingAs($user)->get('/invoice/sepa');

        $sepaDir = storage_path('SEPA');
        $files = glob("$sepaDir/TEST*.xml");
        $xmlContent = file_get_contents($files[0]);
        $xml = simplexml_load_string($xmlContent);
        $xml->registerXPathNamespace('pain', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');

        // Check number of transactions
        $transactions = $xml->xpath('//pain:DrctDbtTxInf');
        $this->assertCount(3, $transactions, 'Should have 3 transactions');

        // Check number of transactions in header
        $nbOfTxs = $xml->xpath('//pain:PmtInf/pain:NbOfTxs');
        $this->assertEquals('3', (string) $nbOfTxs[0]);
    }
}
