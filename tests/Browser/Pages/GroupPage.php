<?php

namespace Tests\Browser\Pages;

use App\Models\Group;
use App\Models\Product;

/**
 * Group Page Object
 * Encapsulates group page interactions for browser tests
 */
class GroupPage
{
    /**
     * Get the URL for the group page
     */
    public static function url(): string
    {
        return '/group';
    }

    /**
     * Create a new group via the UI
     */
    public static function createGroup($page, string $name, string $date)
    {
        $page->type('input[placeholder="Search or Add"]', $name)
            ->type('input[name="groupdate"]', $date)
            ->press('Add Group')
            ->waitForText($name . ' ' . $date);

        return $page;
    }

    /**
     * Open order modal for a group
     */
    public static function openOrderModal($page, Group $group)
    {
        $page->click('button[data-id="' . $group->id . '"]:first-child')
            ->waitForText($group->name)
            ->assertSee('Orders');

        return $page;
    }

    /**
     * Create an order in the modal
     */
    public static function createOrder($page, int $amount, Product $product)
    {
        $page->type('input[name="amount"]', (string)$amount)
            ->select('select[name="product"]', (string)$product->id)
            ->press('Add');

        return $page;
    }

    /**
     * Switch to group members tab in modal
     */
    public static function switchToMembersTab($page)
    {
        $page->click('text=Group Members')
            ->assertSee('Add Member');

        return $page;
    }

    /**
     * Search for groups
     */
    public static function search($page, string $query)
    {
        $page->click('input[placeholder="Search or Add"]')
            ->keys('input[placeholder="Search or Add"]', 'Control+A')
            ->type('input[placeholder="Search or Add"]', $query);

        return $page;
    }
}
