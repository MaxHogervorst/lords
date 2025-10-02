<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Sentinel;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_create_product()
    {
        $this->json('POST', '/product', ['name' => 'Sally'])
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'producttest@example.com',
            'password' => 'password',
        ]);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->json('POST', '/product', ['name' => 'Sally'])
            ->assertDontSee('Whoops')
            ->assertJsonMissing(['success' => true])
            ->assertJsonStructure(['errors']);

        $this->actingAs($user)
            ->withSession([])
            ->json('POST', '/product', ['name' => 'Sally', 'productPrice' => '3.56'])
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
                'name' => 'Sally',
                'price' => '3.56',
            ]);

        $this->assertDatabaseHas('products', ['name' => 'Sally', 'price' => '3.56']);
    }

    public function test_edit_product()
    {
        $product = factory(\App\Models\Product::class)->create([
            'name' => 'Sally',
            'price' => 3.56,
        ]);

        $this->json('PUT', '/product/'.$product->id, ['name' => 'Max'])
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'productedit@example.com',
            'password' => 'password',
        ]);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->json('PUT', '/product/'.$product->id, ['productName' => 'Max', 'productPrice' => null])
            ->assertDontSee('Whoops')
            ->assertJsonMissing(['success' => true])
            ->assertJsonStructure(['errors']);

        $this->actingAs($user)
            ->withSession([])
            ->json('PUT', '/product/'.$product->id, ['productName' => 'Max', 'productPrice' => 3.56])
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Max', 'price' => 3.56]);
    }

    public function test_delete_product()
    {
        $product = factory(\App\Models\Product::class)->create([
            'name' => 'Sally',
            'price' => 3.56,
        ]);

        $this->json('DELETE', '/product/'.$product->id)
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        Sentinel::logout();
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'productdelete@example.com',
            'password' => 'password',
        ]);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->json('DELETE', '/product/'.$product->id)
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
