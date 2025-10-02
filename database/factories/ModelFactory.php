<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});
$factory->define(App\Models\Order::class, function (Faker\Generator $faker) {
    return [
        'invoice_group_id' => function () {
            return factory(App\Models\InvoiceGroup::class)->create()->id;
        },
        'ownerable_id' => $faker->randomNumber(),
        'ownerable_type' => $faker->word,
        'product_id' => function () {
            return factory(App\Models\Product::class)->create()->id;
        },
        'amount' => $faker->randomNumber(),
    ];
});

$factory->define(App\Models\InvoiceGroup::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'status' => $faker->boolean,
    ];
});

$factory->define(App\Models\Member::class, function (Faker\Generator $faker) {
    return [
        'firstname' => $faker->firstName,
        'lastname' => $faker->lastName,
        'iban' => $faker->word,
        'bic' => $faker->word,
        'had_collection' => $faker->boolean,
    ];
});

$factory->define(App\Models\InvoiceProductPrice::class, function (Faker\Generator $faker) {
    return [
        'invoice_product_id' => function () {
            return factory(App\Models\InvoiceProduct::class)->create()->id;
        },
        'price' => $faker->randomFloat(2, 1, 99), // 2 decimals, between 1 and 99
        'description' => $faker->text,
    ];
});

$factory->define(App\Models\InvoiceProduct::class, function (Faker\Generator $faker) {
    return [
        'invoice_group_id' => function () {
            return factory(App\Models\InvoiceGroup::class)->create()->id;
        },
        'name' => $faker->name,
    ];
});

$factory->define(App\Models\GroupMember::class, function (Faker\Generator $faker) {
    return [
        'group_id' => $faker->randomNumber(),
        'member_id' => $faker->randomNumber(),
    ];
});

$factory->define(App\Models\Group::class, function (Faker\Generator $faker) {
    return [
        'invoice_group_id' => function () {
            return factory(App\Models\InvoiceGroup::class)->create()->id;
        },
        'name' => $faker->name,
    ];
});

$factory->define(App\Models\Product::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'price' => $faker->randomFloat(2, 1, 99), // 2 decimals, between 1 and 99
    ];
});

$factory->define(App\Models\InvoiceLine::class, function (Faker\Generator $faker) {
    return [
        'invoice_product_price_id' => function () {
            return factory(App\Models\InvoiceProductPrice::class)->create()->id;
        },
        'member_id' => function () {
            return factory(App\Models\Member::class)->create()->id;
        },
    ];
});
