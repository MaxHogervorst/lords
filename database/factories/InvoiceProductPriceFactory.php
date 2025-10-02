<?php

namespace Database\Factories;

use App\Models\InvoiceProduct;
use App\Models\InvoiceProductPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceProductPriceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InvoiceProductPrice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'invoice_product_id' => InvoiceProduct::factory(),
            'price' => $this->faker->randomFloat(2, 1, 99),
            'description' => $this->faker->text(),
        ];
    }
}
