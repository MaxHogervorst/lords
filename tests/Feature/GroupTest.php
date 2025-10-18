<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\InvoiceGroup;
use App\Models\Member;
use App\Models\Product;
use Tests\TestCase;

class GroupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear cache
        \Cache::flush();

        // Create required data for tests
        Product::factory()->create();
        InvoiceGroup::factory()->create(['status' => true]);
    }

    public function test_create_group()
    {
        $this->json('POST', '/group', ['name' => 'Sally'])
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        $user = \App\Models\User::factory()->create([
            'email' => 'grouptest@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user)
            ->withSession([])
            ->json('POST', '/group', ['name' => null])
            ->assertDontSee('Whoops')
            ->assertJsonMissing(['success' => true])
            ->assertJsonStructure(['errors']);

        $name = 'Sally ' . date('d-m-Y');
        $this->actingAs($user)
            ->withSession([])
            ->json('POST', '/group', ['name' => 'Sally', 'groupdate' => date('Y-m-d')])
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
                'name' => $name,
            ]);

        $this->assertDatabaseHas('groups', ['name' => $name]);
    }

    public function test_edit_group()
    {
        $name = 'Sally ' . date('d-m-Y');
        $name2 = 'Max ' . date('d-m-Y');
        $group = Group::factory()->create([
            'name' => $name,
        ]);

        $this->json('PUT', '/group/' . $group->id, ['name' => $name2])
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        $user = \App\Models\User::factory()->create([
            'email' => 'groupedit@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user)
            ->withSession([])
            ->json('PUT', '/group/' . $group->id, ['name' => null])
            ->assertDontSee('Whoops')
            ->assertJsonMissing(['success' => true])
            ->assertJsonStructure(['errors']);

        $this->actingAs($user)
            ->withSession([])
            ->json('PUT', '/group/' . $group->id, ['name' => $name2])
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('groups', ['id' => $group->id, 'name' => $name2]);
    }

    public function test_delete_group()
    {
        $name = 'Sally ' . date('d-m-Y');
        $group = Group::factory()->create([
            'name' => $name,
        ]);

        $this->json('DELETE', '/group/' . $group->id)
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        $user = \App\Models\User::factory()->create([
            'email' => 'groupdelete@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user)
            ->withSession([])
            ->json('DELETE', '/group/' . $group->id)
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    }

    public function test_group_members()
    {
        $name = 'Sally ' . date('d-m-Y');
        $group = Group::factory()->create([
            'name' => $name,
        ]);

        $member = Member::factory()->create([
            'firstname' => 'Sally',
            'lastname' => 'Test',
        ]);

        $this->json('POST', '/group/addmember', ['groupid' => $group->id, 'member' => $member->id])
            ->assertDontSee('Whoops')
            ->assertSee('Unauthorized.');

        $user = \App\Models\User::factory()->create([
            'email' => 'groupmember@example.com',
            'password' => bcrypt('password'),
        ]);

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
        $name = 'Sally ' . date('d-m-Y');
        $group = Group::factory()->create([
            'name' => $name,
        ]);

        $member = Member::factory()->create([
            'firstname' => 'Sally',
            'lastname' => 'Test',
        ]);

        $group_member = GroupMember::factory()->create([
            'group_id' => $group->id,
            'member_id' => $member->id,
        ]);

        $this->json('DELETE', '/group/groupmember/' . $group_member->id)
            ->assertStatus(401);

        $user = \App\Models\User::factory()->create([
            'email' => 'groupmemberdelete@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user)
            ->withSession([])
            ->json('DELETE', '/group/groupmember/' . $group_member->id)
            ->assertDontSee('Whoops')
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('group_member', ['id' => $group_member->id]);
    }
}
