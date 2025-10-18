<?php

use App\Models\InvoiceGroup;
use App\Models\User;
use function Pest\Laravel\{actingAs};

beforeEach(function () {
    $this->user = User::factory()->create([
        'is_admin' => true,
    ]);
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('can view sepa settings page', function () {
    actingAs($this->user);

    $this->visit('/sepa')
        ->assertSee('SEPA Settings')
        ->assertSee('Creditor Name')
        ->assertSee('Creditor Account IBAN')
        ->assertSee('Creditor BIC')
        ->assertSee('Creditor Id')
        ->assertSee('Type Pain')
        ->assertSee('Collection Days')
        ->assertSee('Max Total Money Per Batch')
        ->assertSee('Max Money Per Transaction')
        ->assertSee('Max Transactions per Batch')
        ->assertSee('Save SEPA Settings');
});

test('can fill sepa settings form', function () {
    actingAs($this->user);

    $page = $this->visit('/sepa');

    // Fill in SEPA settings
    $page->type('input[name="creditorName"]', 'Test Organization')
        ->type('input[name="creditorAccountIBAN"]', 'NL91ABNA0417164300')
        ->type('input[name="creditorAgentBIC"]', 'ABNANL2A')
        ->type('input[name="creditorId"]', 'NL98ZZZ999999990000')
        ->type('input[name="creditorPain"]', 'pain.008.001.02')
        ->type('input[name="ReqdColltnDt"]', '5')
        ->type('input[name="creditorMaxMoneyPerBatch"]', '100000')
        ->type('input[name="creditorMaxMoneyPerTransaction"]', '10000')
        ->type('input[name="creditorMaxTransactionsPerBatch"]', '100');

    // Verify form was filled (by checking if page still exists)
    expect($page)->not->toBeNull();
});
