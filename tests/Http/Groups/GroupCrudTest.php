<?php

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'groupcrud@example.com',
        'password' => bcrypt('password'),
    ]);
    $this->actingAs($this->user);
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('create group successfully via JSON', function () {
    $response = $this
        ->json('POST', '/group', [
            'name' => 'Party',
            'groupdate' => '2025-10-02',
        ]);

    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('groups', [
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);
});

test('create group validates required fields', function () {
    $response = $this
        ->json('POST', '/group', []);

    $response->assertJsonStructure(['errors']);
});

test('show group returns JSON with group data', function () {
    $group = Group::factory()->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $response = $this
        ->json('GET', "/group/{$group->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'group',
            'products',
            'members',
            'groupMembers',
            'orders',
            'orderTotals',
            'currentMonth',
        ]);
});

test('edit group returns JSON with group data', function () {
    $group = Group::factory()->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $response = $this
        ->json('GET', "/group/{$group->id}/edit");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
        ]);
});

test('update group successfully', function () {
    $group = Group::factory()->create([
        'name' => 'Old Name',
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $response = $this
        ->json('PUT', "/group/{$group->id}", [
            'name' => 'New Name',
        ]);

    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'name' => 'New Name',
    ]);
});

test('delete group successfully', function () {
    $group = Group::factory()->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $response = $this
        ->json('DELETE', "/group/{$group->id}");

    $response->assertJson(['success' => true]);

    $this->assertDatabaseMissing('groups', [
        'id' => $group->id,
    ]);
});

test('add member to group successfully', function () {
    $group = Group::factory()->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);
    $member = Member::factory()->create();

    $response = $this
        ->json('POST', '/group/addmember', [
            'groupid' => $group->id,
            'member' => $member->id,
        ]);

    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('group_member', [
        'group_id' => $group->id,
        'member_id' => $member->id,
    ]);
});

test('remove member from group successfully', function () {
    $group = Group::factory()->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);
    $member = Member::factory()->create();
    $groupMember = GroupMember::factory()->create([
        'group_id' => $group->id,
        'member_id' => $member->id,
    ]);

    $response = $this
        ->json('DELETE', "/group/groupmember/{$groupMember->id}");

    $response->assertJson(['success' => true]);

    $this->assertDatabaseMissing('group_member', [
        'id' => $groupMember->id,
    ]);
});
