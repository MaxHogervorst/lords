<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Sentinel;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an active invoice group for tests that need it
        factory(\App\Models\InvoiceGroup::class)->create(['status' => true]);
    }

    public function test_create_group()
    {
        $this->json('POST', '/group', ['name' => 'Sally'])
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'grouptest@example.com',
            'password' => 'password',
        ]);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->json('POST', '/group', ['name' => null])
            ->assertDontSee('Whoops')
            ->assertJsonMissing(['success' => true])
            ->assertJsonStructure(['errors']);
        $name = 'Sally '.date('d-m-Y');
        $this->actingAs($user)
            ->withSession([])
            ->json('POST', '/group', ['name' => 'Sally'])
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
                'name' => $name,
            ]);

        $this->assertDatabaseHas('groups', ['name' => $name]);
    }

    public function test_edit_group()
    {
        $name = 'Sally '.date('d-m-Y');
        $name2 = 'Max '.date('d-m-Y');
        $group = factory(\App\Models\Group::class)->create([
            'name' => $name,
        ]);

        $this->json('PUT', '/group/'.$group->id, ['name' => $name2])
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'groupedit@example.com',
            'password' => 'password',
        ]);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->json('PUT', '/group/'.$group->id, ['name' => null])
            ->assertDontSee('Whoops')
            ->assertJsonMissing(['success' => true])
            ->assertJsonStructure(['errors']);

        $this->actingAs($user)
            ->withSession([])
            ->json('PUT', '/group/'.$group->id, ['name' => $name2])
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('groups', ['id' => $group->id, 'name' => $name2]);
    }

    public function test_delete_group()
    {
        $name = 'Sally '.date('d-m-Y');
        $group = factory(\App\Models\Group::class)->create([
            'name' => $name,
        ]);

        $this->json('DELETE', '/group/'.$group->id)
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        Sentinel::logout();
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'groupdelete@example.com',
            'password' => 'password',
        ]);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->json('DELETE', '/group/'.$group->id)
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    }

    public function test_group_members()
    {
        $name = 'Sally '.date('d-m-Y');
        $group = factory(\App\Models\Group::class)->create([
            'name' => $name,
        ]);

        $member = factory(\App\Models\Member::class)->create([
            'firstname' => 'Sally',
            'lastname' => 'Test',
        ]);

        $this->json('POST', '/group/addmember', ['groupid' => $group->id, 'member' => $member->id])
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        Sentinel::logout();
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'groupmember@example.com',
            'password' => 'password',
        ]);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->json('POST', '/group/addmember', ['groupid' => $group->id, 'member' => null])
            ->assertDontSee('Whoops')
            ->assertJsonMissing(['success' => true])
            ->assertJsonStructure(['errors']);

        $this->actingAs($user)
            ->withSession([])
            ->json('POST', '/group/addmember', ['groupid' => $group->id, 'member' => $member->id])
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('group_member', ['group_id' => $group->id, 'member_id' => $member->id]);
    }

    public function test_delete_group_member()
    {
        $name = 'Sally '.date('d-m-Y');
        $group = factory(\App\Models\Group::class)->create([
            'name' => $name,
        ]);

        $member = factory(\App\Models\Member::class)->create([
            'firstname' => 'Sally',
            'lastname' => 'Test',
        ]);

        $group_member = factory(\App\Models\GroupMember::class)->create([
            'group_id' => $group->id,
            'member_id' => $member->id,
        ]);

        $this->json('GET', '/group/deletegroupmember/'.$group_member->id)
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        Sentinel::logout();
        $sentinelUser = Sentinel::registerAndActivate([
            'email' => 'groupmemberdelete@example.com',
            'password' => 'password',
        ]);
        Sentinel::login($sentinelUser);
        $user = \App\Models\User::find($sentinelUser->id);

        $this->actingAs($user)
            ->withSession([])
            ->json('GET', '/group/deletegroupmember/'.$group_member->id)
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('group_member', ['id' => $group_member->id]);
    }
}
