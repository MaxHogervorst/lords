<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Group;
use App\Models\Member;
use Illuminate\Database\Eloquent\Collection;

class MemberService
{
    /**
     * Get all members with relationships.
     */
    public function getAllWithRelations(array $relations = []): Collection
    {
        $query = Member::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get member by ID with relationships.
     */
    public function getByIdWithRelations(int $id, array $relations = []): ?Member
    {
        $query = Member::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    /**
     * Create a new member.
     */
    public function create(array $data): Member
    {
        $member = new Member();
        $member->fill($data);
        $member->save();

        return $member;
    }

    /**
     * Update an existing member.
     */
    public function update(Member $member, array $data): Member
    {
        $member->fill($data);
        $member->save();

        return $member;
    }

    /**
     * Delete a member.
     */
    public function delete(Member $member): bool
    {
        return $member->delete();
    }

    /**
     * Find member by lastname and IBAN.
     */
    public function findByLastnameAndIban(string $lastname, string $iban): ?Member
    {
        return Member::where(['lastname' => $lastname, 'iban' => $iban])->first();
    }

    /**
     * Get members with SEPA recurring status (had collection).
     */
    public function getMembersWithRcur(array $relations = []): Collection
    {
        $query = Member::whereNotNull('bic')
            ->whereNotNull('iban')
            ->rcur();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get members with SEPA first-time status (no collection yet).
     */
    public function getMembersWithFrst(array $relations = []): Collection
    {
        $query = Member::whereNotNull('bic')
            ->whereNotNull('iban')
            ->frst();

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get members without bank information.
     */
    public function getMembersWithoutBankInfo(): Collection
    {
        return Member::whereNull('bic')
            ->whereNull('iban')
            ->get();
    }

    /**
     * Add member to a group.
     */
    public function addToGroup(Member $member, Group $group): void
    {
        if (!$member->groups->contains($group->id)) {
            $member->groups()->attach($group->id);
        }
    }

    /**
     * Remove member from a group.
     */
    public function removeFromGroup(Member $member, Group $group): void
    {
        $member->groups()->detach($group->id);
    }

    /**
     * Check if member has bank information.
     */
    public function hasBankInfo(Member $member): bool
    {
        return !empty($member->bic) && !empty($member->iban);
    }

    /**
     * Update member's SEPA collection status.
     */
    public function updateCollectionStatus(Member $member, bool $hadCollection): Member
    {
        $member->had_collection = $hadCollection;
        $member->save();

        return $member;
    }
}
