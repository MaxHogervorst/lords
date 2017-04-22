<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProductTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateProduct()
    {
        $this->json('POST', '/product', [ 'name' => 'Sally'])
            ->dontSee('Whoops')
            ->see('Unauthorized.');

        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('POST', '/product', [ 'name' => 'Sally'])
            ->dontSee('Whoops')
            ->dontSeeJson(['success' => true])
            ->seeJsonStructure(['errors']);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('POST', '/product', [ 'name' => 'Sally', 'productPrice' => '3.56'])
            ->dontSee('Whoops')
            ->seeJson([
                'success' => true,
                'name' => 'Sally',
                'price' => '3.56',
            ])
            ->seeInDatabase('products', [ 'name' => 'Sally', 'price' => '3.56']);
    }

    public function testEditProduct()
    {
        $product = factory(App\Models\Product::class)->create([
            'name' =>  'Sally',
            'price' => 3.56,
        ]);

        $this->json('PUT', '/product/' . $product->id, [ 'name' => 'Max'])
            ->dontSee('Whoops')
            ->see('Unauthorized.');

        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('PUT', '/product/' . $product->id, [ 'productName' => 'Max', 'productPrice' => null])
            ->dontSee('Whoops')
            ->dontSeeJson(['success' => true])
            ->seeJsonStructure(['errors']);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('PUT', '/product/' . $product->id, [ 'productName' => 'Max', 'productPrice' => 3.56])
            ->dontSee('Whoops')
            ->seeJson([
                'success' => true,
            ])
            ->seeInDatabase('products', [ 'id' => $product->id, 'name' => 'Max', 'price' => 3.56]);
    }

    public function testDeleteProduct()
    {
        $product = factory(App\Models\Product::class)->create([
            'name' =>  'Sally',
            'price' => 3.56,
        ]);

        $this->json('DELETE', '/product/' . $product->id)
            ->dontSee('Whoops')
            ->see('Unauthorized.');

        Sentinel::logout();
        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('DELETE', '/product/' . $product->id)
            ->dontSee('Whoops')
            ->seeJson([
                'success' => true,
            ])
            ->dontSeeInDatabase('products', [ 'id' => $product->id]);
    }
}
