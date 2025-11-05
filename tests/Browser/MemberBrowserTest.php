<?php

use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\User;

use function Pest\Laravel\{actingAs};

use Tests\Browser\Pages\MemberPage;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->invoiceGroup = InvoiceGroup::factory()->create(['status' => true]);
});

test('can create a new member via the UI', function () {
    actingAs($this->user);

    $page = $this->visit(MemberPage::url())
        ->assertSee('Add Member');

    MemberPage::createMember($page, 'John', 'Doe');

    $page->assertSee('Doe');

    expect(Member::where('firstname', 'John')->where('lastname', 'Doe')->exists())->toBeTrue();
});

test('can view member list', function () {
    actingAs($this->user);

    $member = Member::factory()->create(['firstname' => 'Alice', 'lastname' => 'Smith']);

    $this->visit('/member')
        ->assertSee('Alice')
        ->assertSee('Smith');
});

test('shows admin features for admin users', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    actingAs($admin);

    Member::factory()->create(['firstname' => 'Test', 'lastname' => 'User', 'bic' => 'ABNANL2A']);

    $this->visit('/member')
        ->assertSee('Filter Bankinfo')
        ->assertSee('Filter Had Collection');
});

test('hides admin features for non-admin users', function () {
    actingAs($this->user);

    $this->visit('/member')
        ->assertDontSee('Filter Bankinfo')
        ->assertDontSee('Filter Had Collection');
});

test('can open member edit modal', function () {
    actingAs($this->user);

    $member = Member::factory()->create([
        'firstname' => 'Edit',
        'lastname' => 'TestMember',
        'bic' => 'ABNANL2A',
        'iban' => 'NL91ABNA0417164300',
    ]);

    $page = $this->visit('/member')
        ->assertSee('Edit')
        ->assertSee('TestMember');

    // Find and click the edit button using data-testid
    $page->click('[data-testid="member-edit-' . $member->id . '"]')
        ->waitForText('First Name', 10)
        ->assertSee('Edit')
        ->assertSee('TestMember')
        ->assertVisible('#member-edit')
        ->assertVisible('[data-testid="member-firstname-input"]')
        ->assertVisible('[data-testid="member-lastname-input"]');
});

test('can edit a member and see optimistic update', function () {
    actingAs($this->user);

    $member = Member::factory()->create([
        'firstname' => 'Original',
        'lastname' => 'Name',
        'bic' => 'ABNANL2A',
        'iban' => 'NL91ABNA0417164300',
    ]);

    $page = $this->visit(MemberPage::url())
        ->assertSee('Original')
        ->assertSee('Name');

    MemberPage::openEditModal($page, $member);

    MemberPage::fillEditForm($page, [
        'firstname' => 'Updated',
        'lastname' => 'Person',
        'bic' => 'INGBNL2A',
    ]);

    MemberPage::saveEdit($page);

    // Verify in database
    $member->refresh();
    expect($member->firstname)->toBe('Updated');
    expect($member->lastname)->toBe('Person');
    expect($member->bic)->toBe('INGBNL2A');

    MemberPage::closeEditModal($page);

    // Verify optimistic update in UI
    $page->waitForText('Updated', 3)
        ->assertSee('Person');
});

test('can delete a member and see optimistic removal', function () {
    actingAs($this->user);

    $member = Member::factory()->create([
        'firstname' => 'ToDelete',
        'lastname' => 'Member',
    ]);

    $page = $this->visit(MemberPage::url())
        ->assertSee('ToDelete')
        ->assertSee('Member');

    MemberPage::openEditModal($page, $member);
    MemberPage::clickDelete($page);
    MemberPage::confirmDelete($page);

    // Verify deleted from database
    expect(Member::find($member->id))->toBeNull();

    // Verify optimistic removal from UI
    $page->assertDontSee('ToDelete Member');
});
