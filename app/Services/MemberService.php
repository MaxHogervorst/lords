<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Group;
use App\Models\Member;
use App\Repositories\MemberRepository;
use Illuminate\Database\Eloquent\Collection;

class MemberService
{
    public function __construct(
        private readonly MemberRepository $memberRepository
    ) {}
    /**
     * Get all members with relationships.
     */
    public function getAllWithRelations(array $relations = []): Collection
    {
        return $this->memberRepository->all(['*'], $relations);
    }

    /**
     * Get member by ID with relationships.
     */
    public function getByIdWithRelations(int $id, array $relations = []): ?Member
    {
        return $this->memberRepository->find($id, ['*'], $relations);
    }

    /**
     * Create a new member.
     */
    public function create(array $data): Member
    {
        return $this->memberRepository->create($data);
    }

    /**
     * Update an existing member.
     */
    public function update(Member $member, array $data): Member
    {
        return $this->memberRepository->update($member, $data);
    }

    /**
     * Delete a member.
     */
    public function delete(Member $member): bool
    {
        return $this->memberRepository->delete($member);
    }

    /**
     * Find member by lastname and IBAN.
     */
    public function findByLastnameAndIban(string $lastname, string $iban): ?Member
    {
        return $this->memberRepository->findByLastnameAndIban($lastname, $iban);
    }

    /**
     * Get members with SEPA recurring status (had collection).
     */
    public function getMembersWithRcur(array $relations = []): Collection
    {
        return $this->memberRepository->getMembersWithRcur($relations);
    }

    /**
     * Get members with SEPA first-time status (no collection yet).
     */
    public function getMembersWithFrst(array $relations = []): Collection
    {
        return $this->memberRepository->getMembersWithFrst($relations);
    }

    /**
     * Get members without bank information.
     */
    public function getMembersWithoutBankInfo(): Collection
    {
        return $this->memberRepository->getMembersWithoutBankInfo();
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
