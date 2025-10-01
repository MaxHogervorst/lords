<?php

namespace Tests\Feature;

use Tests\TestCase;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Sentinel;

class MemberTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateMember()
    {
        $this->json('POST', '/member', [ 'name' => 'Sally'])
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'membertest@example.com',
            'password' => 'password',
        ]);
        Sentinel::login($sentinelUser);
        $user = \App\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->json('POST', '/member', [ 'name' => 'Sally'])
            ->assertDontSee('Whoops')
            ->assertJsonMissing(['success' => true])
            ->assertJsonStructure(['errors']);

        $this->actingAs($user)
            ->withSession([])
            ->json('POST', '/member', [ 'name' => 'Sally', 'lastname' => 'Test'])
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
                'firstname' => 'Sally',
                'lastname' => 'Test',
            ]);

        $this->assertDatabaseHas('members', [ 'firstname' => 'Sally', 'lastname' => 'Test']);
    }

    public function testEditMember()
    {
        $member = factory(\App\Models\Member::class)->create([
            'firstname' =>  'Sally',
            'lastname' => 'Test',
        ]);

        $this->json('PUT', '/member/' . $member->id, [ 'firstname' => 'Max'])
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'memberedit@example.com',
            'password' => 'password',
        ]);
        Sentinel::login($sentinelUser);
        $user = \App\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->json('PUT', '/member/' . $member->id, [ 'firstname' => 'Max', 'lastname' => null])
            ->assertDontSee('Whoops')
            ->assertJsonMissing(['success' => true])
            ->assertJsonStructure(['errors']);

        $this->actingAs($user)
            ->withSession([])
            ->json('PUT', '/member/' . $member->id, [ 'name' => 'Max', 'lastname' => 'Hogervorst', 'bic' => 'bic', 'iban' => 'iban'])
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('members', [ 'id' => $member->id, 'firstname' => 'Max', 'lastname' => 'Hogervorst', 'bic' => 'bic', 'iban' => 'iban']);
    }

    public function testDeleteMember()
    {
        $member = factory(\App\Models\Member::class)->create([
            'firstname' =>  'Sally',
            'lastname' => 'Test',
        ]);

        $this->json('DELETE', '/member/' . $member->id)
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        Sentinel::logout();
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'memberdelete@example.com',
            'password' => 'password',
        ]);
        Sentinel::login($sentinelUser);
        $user = \App\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->json('DELETE', '/member/' . $member->id)
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('members', [ 'id' => $member->id]);
    }
}
