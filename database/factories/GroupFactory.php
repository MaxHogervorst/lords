<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\InvoiceGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Group::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'invoice_group_id' => InvoiceGroup::factory(),
            'name' => $this->faker->name(),
        ];
    }
}
