<?php

use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'membercrud@example.com',
        'password' => bcrypt('password'),
    ]);
    $this->actingAs($this->user);
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('create member successfully via JSON', function () {
    $response = $this->json('POST', '/member', [
        'name' => 'John',
        'lastname' => 'Doe',
    ]);

    $response->assertJson([
        'success' => true,
        'firstname' => 'John',
        'lastname' => 'Doe',
    ]);

    $this->assertDatabaseHas('members', [
        'firstname' => 'John',
        'lastname' => 'Doe',
    ]);
});

test('create member validates required fields', function () {
    $response = $this->json('POST', '/member', [
        'name' => 'John',
    ]);

    $response->assertJsonStructure(['errors']);
});

test('show member page loads', function () {
    $member = Member::factory()->create();

    $response = $this->get("/member/{$member->id}");

    $response->assertStatus(200)
        ->assertViewIs('member.order')
        ->assertViewHas('member');
});

test('edit member page loads', function () {
    $member = Member::factory()->create();

    $response = $this->get("/member/{$member->id}/edit");

    $response->assertStatus(200)
        ->assertViewIs('member.edit')
        ->assertViewHas('member');
});

test('update member successfully', function () {
    $member = Member::factory()->create([
        'firstname' => 'John',
        'lastname' => 'Doe',
    ]);

    $response = $this->json('PUT', "/member/{$member->id}", [
        'name' => 'Jane',
        'lastname' => 'Smith',
    ]);

    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('members', [
        'id' => $member->id,
        'firstname' => 'Jane',
        'lastname' => 'Smith',
    ]);
});

test('delete member successfully', function () {
    $member = Member::factory()->create();

    $response = $this->json('DELETE', "/member/{$member->id}");

    $response->assertJson(['success' => true]);

    $this->assertDatabaseMissing('members', [
        'id' => $member->id,
    ]);
});
