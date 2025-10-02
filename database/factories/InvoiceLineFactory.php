<?php

namespace Database\Factories;

use App\Models\InvoiceLine;
use App\Models\InvoiceProductPrice;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceLineFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InvoiceLine::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'invoice_product_price_id' => InvoiceProductPrice::factory(),
            'member_id' => Member::factory(),
        ];
    }
}
