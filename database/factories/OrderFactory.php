<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\InvoiceGroup;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'invoice_group_id' => InvoiceGroup::factory(),
            'ownerable_id' => $this->faker->randomNumber(),
            'ownerable_type' => $this->faker->word(),
            'product_id' => Product::factory(),
            'amount' => $this->faker->randomNumber(),
        ];
    }
}
