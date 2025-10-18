<?php

use App\Models\Group;
use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'order@example.com',
        'password' => bcrypt('password'),
    ]);
    $this->actingAs($this->user);
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
    $this->product = Product::factory()->create();
});

test('create order for member successfully', function () {
    $member = Member::factory()->create();

    $response = $this
        ->json('POST', '/order/store/Member', [
            'memberId' => $member->id,
            'product' => $this->product->id,
            'amount' => 3,
        ]);

    $response->assertJson([
        'success' => true,
        'product' => $this->product->name,
        'amount' => 3,
    ]);

    $this->assertDatabaseHas('orders', [
        'ownerable_id' => $member->id,
        'ownerable_type' => 'App\Models\Member',
        'product_id' => $this->product->id,
        'amount' => 3,
    ]);
});

test('create order for group successfully', function () {
    $group = Group::factory()->create([
        'invoice_group_id' => $this->invoiceGroup->id,
    ]);

    $response = $this
        ->json('POST', '/order/store/Group', [
            'memberId' => $group->id,
            'product' => $this->product->id,
            'amount' => 5,
        ]);

    $response->assertJson([
        'success' => true,
        'product' => $this->product->name,
        'amount' => 5,
    ]);

    $this->assertDatabaseHas('orders', [
        'ownerable_id' => $group->id,
        'ownerable_type' => 'App\Models\Group',
        'product_id' => $this->product->id,
        'amount' => 5,
    ]);
});

test('create order validates required fields', function () {
    $response = $this
        ->json('POST', '/order/store/Member', [
            'memberId' => 1,
        ]);

    $response->assertJsonStructure(['errors']);
});

test('create order requires authentication', function () {
    auth()->logout();
    $member = Member::factory()->create();

    $response = $this->json('POST', '/order/store/Member', [
        'memberId' => $member->id,
        'product' => $this->product->id,
        'amount' => 1,
    ]);

    $response->assertStatus(401);
});
