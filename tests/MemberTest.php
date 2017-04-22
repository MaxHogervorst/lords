<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

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
            ->dontSee('Whoops')
            ->see('Unauthorized.');

        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('POST', '/member', [ 'name' => 'Sally'])
            ->dontSee('Whoops')
            ->dontSeeJson(['success' => true])
            ->seeJsonStructure(['errors']);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('POST', '/member', [ 'name' => 'Sally', 'lastname' => 'Test'])
            ->dontSee('Whoops')
            ->seeJson([
                'success' => true,
                'firstname' => 'Sally',
                'lastname' => 'Test',
            ])
            ->seeInDatabase('members', [ 'firstname' => 'Sally', 'lastname' => 'Test'])
        ;
    }

    public function testEditMember()
    {
        $member = factory(App\Models\Member::class)->create([
            'firstname' =>  'Sally',
            'lastname' => 'Test',
        ]);

        $this->json('PUT', '/member/' . $member->id, [ 'firstname' => 'Max'])
            ->dontSee('Whoops')
            ->see('Unauthorized.');

        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('PUT', '/member/' . $member->id, [ 'firstname' => 'Max', 'lastname' => null])
            ->dontSee('Whoops')
            ->dontSeeJson(['success' => true])
            ->seeJsonStructure(['errors']);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('PUT', '/member/' . $member->id, [ 'name' => 'Max', 'lastname' => 'Hogervorst', 'bic' => 'bic', 'iban' => 'iban'])
            ->dontSee('Whoops')
            ->seeJson([
                'success' => true,
            ])
            ->seeInDatabase('members', [ 'id' => $member->id, 'firstname' => 'Max', 'lastname' => 'Hogervorst', 'bic' => 'bic', 'iban' => 'iban']);
    }

    public function testDeleteMember()
    {
        $member = factory(App\Models\Member::class)->create([
            'firstname' =>  'Sally',
            'lastname' => 'Test',
        ]);

        $this->json('DELETE', '/member/' . $member->id)
            ->dontSee('Whoops')
            ->see('Unauthorized.');

        Sentinel::logout();
        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('DELETE', '/member/' . $member->id)
            ->dontSee('Whoops')
            ->seeJson([
                'success' => true,
            ])
            ->dontSeeInDatabase('members', [ 'id' => $member->id]);
    }
}
