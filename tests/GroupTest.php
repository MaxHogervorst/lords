<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class GroupTest extends TestCase
{
    use DatabaseTransactions;

    public function testCreateGroup()
    {
        $this->json('POST', '/group', [ 'name' => 'Sally'])
            ->dontSee('Whoops')
            ->see('Unauthorized.');

        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('POST', '/group', [ 'name' => null])
            ->dontSee('Whoops')
            ->dontSeeJson(['success' => true])
            ->seeJsonStructure(['errors']);
        $name = 'Sally ' . date('d-m-Y');
        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('POST', '/group', [ 'name' => 'Sally'])
            ->dontSee('Whoops')
            ->seeJson([
                'success' => true,
                'name' => $name,
            ])
            ->seeInDatabase('groups', [ 'name' => $name]);
    }

    public function testEditGroup()
    {
        $name = 'Sally ' . date('d-m-Y');
        $name2 = 'Max ' . date('d-m-Y');
        $group = factory(App\Models\Group::class)->create([
            'name' =>  $name,
        ]);

        $this->json('PUT', '/group/' . $group->id, [ 'name' => $name2])
            ->dontSee('Whoops')
            ->see('Unauthorized.');

        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('PUT', '/group/' . $group->id, [ 'name' => null])
            ->dontSee('Whoops')
            ->dontSeeJson(['success' => true])
            ->seeJsonStructure(['errors']);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('PUT', '/group/' . $group->id, [ 'name' => $name2])
            ->dontSee('Whoops')
            ->seeJson([
                'success' => true,
            ])
            ->seeInDatabase('groups', [ 'id' => $group->id, 'name' => $name2]);
    }

    public function testDeleteGroup()
    {
        $name = 'Sally ' . date('d-m-Y');
        $group = factory(App\Models\Group::class)->create([
            'name' =>  $name,
        ]);

        $this->json('DELETE', '/group/' . $group->id)
            ->dontSee('Whoops')
            ->see('Unauthorized.');

        Sentinel::logout();
        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('DELETE', '/group/' . $group->id)
            ->dontSee('Whoops')
            ->seeJson([
                'success' => true,
            ])
            ->dontSeeInDatabase('groups', [ 'id' => $group->id]);
    }

    public function testGroupMembers()
    {
        $name = 'Sally ' . date('d-m-Y');
        $group = factory(App\Models\Group::class)->create([
            'name' =>  $name,
        ]);

        $member = factory(App\Models\Member::class)->create([
            'firstname' =>  'Sally',
            'lastname' => 'Test',
        ]);

        $this->json('POST', '/group/addmember', ['groupid' => $group->id, 'member' => $member->id])
            ->dontSee('Whoops')
            ->see('Unauthorized.');

        Sentinel::logout();
        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('POST', '/group/addmember', ['groupid' => $group->id, 'member' => null])
            ->dontSee('Whoops')
            ->dontSeeJson(['success' => true])
            ->seeJsonStructure(['errors']);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('POST', '/group/addmember', ['groupid' => $group->id, 'member' => $member->id])
            ->dontSee('Whoops')
            ->seeJson([
                'success' => true,
            ])
            ->seeInDatabase('group_member', [ 'group_id' => $group->id, 'member_id' => $member->id]);
    }

    public function testDeleteGroupMember()
    {
        $name = 'Sally ' . date('d-m-Y');
        $group = factory(App\Models\Group::class)->create([
            'name' =>  $name,
        ]);

        $member = factory(App\Models\Member::class)->create([
            'firstname' =>  'Sally',
            'lastname' => 'Test',
        ]);

        $group_member = factory(App\Models\GroupMember::class)->create([
            'group_id' =>  $group->id,
            'member_id' => $member->id,
        ]);

        $this->json('GET', '/group/deletegroupmember/' . $group_member->id)
            ->dontSee('Whoops')
            ->see('Unauthorized.');

        Sentinel::logout();
        $user = Sentinel::findById(3);
        Sentinel::login($user);

        $this->actingAs(\App\User::find(3))
            ->withSession([])
            ->json('GET', '/group/deletegroupmember/' . $group_member->id)
            ->dontSee('Whoops')
            ->seeJson([
                'success' => true,
            ])
            ->dontSeeInDatabase('group_member', [ 'id' => $group_member->id]);
    }
}
