<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderTest extends TestCase
{
	use DatabaseTransactions;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateOrder()
    {
		$name = 'Sally ' . date('d-m-Y');
		$group = factory(App\Models\Group::class)->create([
			'name' =>  $name,
		]);
	
		$member = factory(App\Models\Member::class)->create([
			'firstname' =>  'Sally',
			'lastname' => 'Test',
		]);
	
		$this->json('POST', '/order/store/member', [ 'name' => 'Sally'])
			->dontSee('Whoops')
			->see('Unauthorized.');
	
		$user = Sentinel::findById(3);
		Sentinel::login($user);
	
	
		$this->actingAs(\App\User::find(3))
			->withSession([])
			->json('POST', '/order/store/member', [
				'memberId' => $group->id,
				'product' => 1
			])
			->dontSee('Whoops')
			->dontSeeJson(['success' => true])
			->seeJsonStructure(['errors']);
	
		$this->actingAs(\App\User::find(3))
			->withSession([])
			->json('POST', '/order/store/member', [
				'memberId' => $group->id,
				'product' => 1,
				'amount' => 2436
			])
			->dontSee('Whoops')
			->seeJson([
				'success' => true,
				'member_id' => $group->id,
				'product_id' => 1,
				'amount' => 2436
			])
			->seeInDatabase('orders', [ 'product_id' => 1, 'amount' => 2436, 'ownerable_id' => $group->id]);
    }
}
