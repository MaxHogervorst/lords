<?php

use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'memberlist@example.com',
        'password' => bcrypt('password'),
    ]);
    $this->actingAs($this->user);
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('member index page loads', function () {
    $response = $this->get('/member');

    $response->assertStatus(200)
        ->assertViewIs('member.index');
});

test('member index displays members', function () {
    $members = Member::factory()->count(3)->create();

    $response = $this->get('/member');

    $response->assertStatus(200)
        ->assertViewHas('members');

    foreach ($members as $member) {
        $response->assertSee($member->firstname);
    }
});

test('member index requires authentication', function () {
    auth()->logout();

    $response = $this->get('/member');

    $response->assertRedirect('/auth/login');
});
