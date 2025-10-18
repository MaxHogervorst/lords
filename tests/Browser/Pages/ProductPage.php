<?php

namespace Tests\Browser\Pages;

use App\Models\Product;

/**
 * Product Page Object
 * Encapsulates product page interactions for browser tests
 */
class ProductPage
{
    /**
     * Get the URL for the product page
     */
    public static function url(): string
    {
        return '/product';
    }

    /**
     * Create a new product via the UI
     */
    public static function createProduct($page, string $name, float $price)
    {
        $page->type('input[placeholder="Name"]', $name)
            ->type('input[name="price"]', (string)$price)
            ->press('Add Product')
            ->waitForText($name);

        return $page;
    }

    /**
     * Open edit modal for a product
     */
    public static function openEditModal($page, Product $product)
    {
        $page->click('[data-testid="product-edit-' . $product->id . '"]')
            ->waitForText('Product Name', 10);

        return $page;
    }

    /**
     * Fill edit form fields
     */
    public static function fillEditForm($page, array $data)
    {
        if (isset($data['name'])) {
            $page->clear('[data-testid="product-name-input"]')
                ->type('[data-testid="product-name-input"]', $data['name']);
        }

        if (isset($data['price'])) {
            $page->clear('[data-testid="product-price-input"]')
                ->type('[data-testid="product-price-input"]', (string)$data['price']);
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
        $page->click('#product-edit .btn-close');

        return $page;
    }

    /**
     * Search for products
     */
    public static function search($page, string $query)
    {
        $page->type('input[placeholder="Name"]', $query);

        return $page;
    }
}
