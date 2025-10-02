<?php

use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->sentinelUser = \Sentinel::registerAndActivate([
        'email' => 'memberbrowser@example.com',
        'password' => 'password',
    ]);
    \Sentinel::login($this->sentinelUser);
    $this->user = User::find($this->sentinelUser->id);
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('can view member list page', function () {
    Member::factory()->count(3)->create();

    visit('/member')
        ->assertSee('Members')
        ->screenshot(filename: 'member-list');
});

test('can create new member via UI', function () {
    visit('/member')
        ->click('Add Member')
        ->type('name', 'John')
        ->type('lastname', 'Doe')
        ->submit()
        ->assertSee('Member created')
        ->screenshot(filename: 'member-created');

    expect(Member::where('firstname', 'John')->where('lastname', 'Doe')->exists())->toBeTrue();
});

test('can edit existing member', function () {
    $member = Member::factory()->create([
        'firstname' => 'Jane',
        'lastname' => 'Smith',
    ]);

    visit("/member/{$member->id}/edit")
        ->type('name', 'Janet')
        ->submit()
        ->assertSee('Member updated')
        ->screenshot(filename: 'member-updated');

    expect(Member::find($member->id)->firstname)->toBe('Janet');
});

test('can view member details', function () {
    $member = Member::factory()->create([
        'firstname' => 'Bob',
        'lastname' => 'Johnson',
    ]);

    visit("/member/{$member->id}")
        ->assertSee('Bob')
        ->assertSee('Johnson')
        ->screenshot(filename: 'member-details');
});

test('can delete member', function () {
    $member = Member::factory()->create();

    visit('/member')
        ->click('Delete')
        ->assertSee('Member deleted')
        ->screenshot(filename: 'member-deleted');

    expect(Member::find($member->id))->toBeNull();
});
