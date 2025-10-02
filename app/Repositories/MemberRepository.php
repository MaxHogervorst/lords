<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class MemberRepository extends BaseRepository
{
    protected function makeModel(): Model
    {
        return new Member();
    }

    /**
     * Find member by lastname and IBAN.
     */
    public function findByLastnameAndIban(string $lastname, string $iban): ?Member
    {
        return $this->model->newQuery()
            ->where('lastname', $lastname)
            ->where('iban', $iban)
            ->first();
    }

    /**
     * Get members with RCUR status (recurring).
     */
    public function getMembersWithRcur(array $relations = []): Collection
    {
        $query = $this->model->newQuery()
            ->whereNotNull('bic')
            ->whereNotNull('iban')
            ->where('had_collection', true);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get members with FRST status (first time).
     */
    public function getMembersWithFrst(array $relations = []): Collection
    {
        $query = $this->model->newQuery()
            ->whereNotNull('bic')
            ->whereNotNull('iban')
            ->where('had_collection', false);

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
        return $this->model->newQuery()
            ->whereNull('bic')
            ->whereNull('iban')
            ->get();
    }

    /**
     * Get members with bank information.
     */
    public function getMembersWithBankInfo(): Collection
    {
        return $this->model->newQuery()
            ->whereNotNull('bic')
            ->whereNotNull('iban')
            ->get();
    }

    /**
     * Search members by name or email.
     */
    public function search(string $term): Collection
    {
        return $this->model->newQuery()
            ->where('firstname', 'like', "%{$term}%")
            ->orWhere('lastname', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->get();
    }

    /**
     * Get query builder instance (public access for complex queries).
     */
    public function query()
    {
        return $this->model->newQuery();
    }
}
