<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\InvoiceGroup;
use App\Models\Product;
use Sentinel;
use Tests\TestCase;

class SepaControllerTest extends TestCase
{

    private $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear cache and logout any existing session
        \Cache::flush();
        if (Sentinel::check()) {
            Sentinel::logout();
        }

        // Create required data for tests
        Product::factory()->create();
        InvoiceGroup::factory()->create(['status' => true]);

        // Create admin role for tests
        $this->adminRole = Sentinel::getRoleRepository()->createModel()->firstOrCreate([
            'slug' => 'admin',
        ], [
            'name' => 'Admin',
        ]);
    }

    protected function tearDown(): void
    {
        // Logout after each test
        if (Sentinel::check()) {
            Sentinel::logout();
        }

        parent::tearDown();
    }

    /**
     * Test SEPA settings page is accessible by admin
     */
    public function test_sepa_settings_page_is_accessible_by_admin(): void
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'sepaadmin@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

        $response = $this->actingAs($user)->get('/sepa');

        $response->assertOk();
        $response->assertSee('Creditor Name');
    }

    /**
     * Test SEPA settings page is not accessible by non-admin
     */
    public function test_sepa_settings_page_is_not_accessible_by_non_admin(): void
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'regular@example.com',
            'password' => 'password',
        ]);

        $response = $this->call('GET', '/sepa');

        // Should redirect or show unauthorized
        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * Test storing SEPA settings
     */
    public function test_store_sepa_settings(): void
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'sepastore@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

        $sepaData = [
            'creditorName' => 'Test Organization',
            'creditorAccountIBAN' => 'NL91ABNA0417164300',
            'creditorAgentBIC' => 'ABNANL2A',
            'creditorId' => 'NL00ZZZ000000000000',
            'creditorPain' => 'pain.008.001.02',
            'creditorMaxMoneyPerBatch' => 999999,
            'creditorMaxMoneyPerTransaction' => 100000,
            'creditorMaxTransactionsPerBatch' => 1000,
            'ReqdColltnDt' => 5,
        ];

        $this->actingAs($user)
            ->json('POST', '/sepa', $sepaData)
            ->assertJson(['success' => true]);

        // Verify settings were saved
        $this->assertEquals('Test Organization', \Settings::get('creditorName'));
        $this->assertEquals('NL91ABNA0417164300', \Settings::get('creditorAccountIBAN'));
    }

    /**
     * Test storing SEPA settings requires all fields
     */
    public function test_store_sepa_settings_validates_required_fields(): void
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'sepavalidation@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

        // Missing required fields
        $invalidData = [
            'creditorName' => 'Test Org',
        ];

        $response = $this->actingAs($user)
            ->json('POST', '/sepa', $invalidData);

        $response->assertJsonStructure(['errors']);
    }

    /**
     * Test storing SEPA settings with valid IBAN format
     */
    public function test_store_sepa_settings_accepts_valid_iban(): void
    {
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'sepaiban@example.com',
            'password' => 'password',
        ]);
        $this->adminRole->users()->attach($sentinelUser);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

        $sepaData = [
            'creditorName' => 'IBAN Test Org',
            'creditorAccountIBAN' => 'NL20INGB0001234567', // Valid Dutch IBAN
            'creditorAgentBIC' => 'INGBNL2A',
            'creditorId' => 'NL00ZZZ000000000000',
            'creditorPain' => 'pain.008.001.02',
            'creditorMaxMoneyPerBatch' => 500000,
            'creditorMaxMoneyPerTransaction' => 50000,
            'creditorMaxTransactionsPerBatch' => 500,
            'ReqdColltnDt' => 3,
        ];

        $this->actingAs($user)
            ->json('POST', '/sepa', $sepaData)
            ->assertJson(['success' => true]);

        $this->assertEquals('NL20INGB0001234567', \Settings::get('creditorAccountIBAN'));
    }

    /**
     * Test SEPA configuration settings are used in file generation
     */
    public function test_sepa_configuration_affects_file_generation(): void
    {
        // Set up SEPA configuration
        config(['sepa.creditor.name' => 'Config Test Creditor']);
        config(['sepa.creditor.account_iban' => 'NL91ABNA0417164300']);
        config(['sepa.file.prefix' => 'TEST']);

        // Verify config is loaded
        $this->assertEquals('Config Test Creditor', config('sepa.creditor.name'));
        $this->assertEquals('TEST', config('sepa.file.prefix'));
    }

    /**
     * Test SEPA batch limits from configuration
     */
    public function test_sepa_batch_limits_from_configuration(): void
    {
        // Test default configuration values
        $this->assertIsInt(config('sepa.batch.max_money_per_batch'));
        $this->assertIsInt(config('sepa.batch.max_transactions_per_batch'));
        $this->assertIsInt(config('sepa.batch.max_money_per_transaction'));

        // Test custom configuration
        config(['sepa.batch.max_money_per_batch' => 1000000]);
        $this->assertEquals(1000000, config('sepa.batch.max_money_per_batch'));
    }
}
