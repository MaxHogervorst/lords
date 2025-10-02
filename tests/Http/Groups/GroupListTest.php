<?php

use App\Models\Group;
use App\Models\InvoiceGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'grouplist@example.com',
        'password' => 'password',
    ]);
    \Sentinel::login($this->sentinelUser);
    $this->user = User::find($this->sentinelUser->id);
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('group index page loads', function () {
    $response = $this->get('/group');

    $response->assertStatus(200)
        ->assertViewIs('group.index');
});

test('group index displays groups', function () {
    $groups = Group::factory()->count(3)->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $response = $this->get('/group');

    $response->assertStatus(200);

    foreach ($groups as $group) {
        $response->assertSee($group->name);
    }
});

test('group index requires authentication', function () {
    \Sentinel::logout();

    $response = $this->get('/group');

    $response->assertRedirect('/auth/login');
});
