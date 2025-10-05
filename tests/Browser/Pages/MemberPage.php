<?php

namespace Tests\Browser\Pages;

use App\Models\Member;

/**
 * Member Page Object
 * Encapsulates member page interactions for browser tests
 */
class MemberPage
{
    /**
     * Get the URL for the member page
     */
    public static function url(): string
    {
        return '/member';
    }

    /**
     * Create a new member via the UI
     */
    public static function createMember($page, string $firstname, string $lastname)
    {
        $page->type('input[placeholder="First Name"]', $firstname)
            ->type('input[placeholder="Last Name"]', $lastname)
            ->press('Add Member')
            ->waitForText($firstname);

        return $page;
    }

    /**
     * Open edit modal for a member
     */
    public static function openEditModal($page, Member $member)
    {
        $page->click('[data-testid="member-edit-' . $member->id . '"]')
            ->waitForText('First Name', 10);

        return $page;
    }

    /**
     * Fill edit form fields
     */
    public static function fillEditForm($page, array $data)
    {
        if (isset($data['firstname'])) {
            $page->clear('[data-testid="member-firstname-input"]')
                ->type('[data-testid="member-firstname-input"]', $data['firstname']);
        }

        if (isset($data['lastname'])) {
            $page->clear('[data-testid="member-lastname-input"]')
                ->type('[data-testid="member-lastname-input"]', $data['lastname']);
        }

        if (isset($data['bic'])) {
            $page->clear('[data-testid="member-bic-input"]')
                ->type('[data-testid="member-bic-input"]', $data['bic']);
        }

        if (isset($data['iban'])) {
            $page->clear('[data-testid="member-iban-input"]')
                ->type('[data-testid="member-iban-input"]', $data['iban']);
        }

        return $page;
    }

    /**
     * Save the edit form
     */
    public static function saveEdit($page)
    {
        $page->press('Save Changes');
        usleep(500000); // Wait for save operation

        return $page;
    }

    /**
     * Close the edit modal
     */
    public static function closeEditModal($page)
    {
        $page->click('#member-edit .btn-close');

        return $page;
    }

    /**
     * Click delete button in edit modal
     */
    public static function clickDelete($page)
    {
        $page->click('[data-testid="member-delete-button"]')
            ->waitForText('Delete Member', 3);

        return $page;
    }

    /**
     * Confirm deletion in confirmation modal
     */
    public static function confirmDelete($page)
    {
        $page->click('[data-testid="confirm-action-button"]')
            ->waitForText('Actions', 3);

        return $page;
    }

    /**
     * Search for members by first name
     */
    public static function searchByFirstName($page, string $query)
    {
        $page->type('input[placeholder="First Name"]', $query);

        return $page;
    }

    /**
     * Search for members by last name
     */
    public static function searchByLastName($page, string $query)
    {
        $page->type('input[placeholder="Last Name"]', $query);

        return $page;
    }

    /**
     * Toggle bank info filter
     */
    public static function toggleBankInfoFilter($page)
    {
        $page->click('input[x-model="filterBankInfo"]');

        return $page;
    }

    /**
     * Toggle collection filter
     */
    public static function toggleCollectionFilter($page)
    {
        $page->click('input[x-model="filterCollection"]');

        return $page;
    }
}
