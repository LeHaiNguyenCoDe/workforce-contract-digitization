<?php

namespace App\Repositories\Contracts;

use App\Models\LoyaltyAccount;

interface LoyaltyRepositoryInterface
{
    /**
     * Find or create loyalty account by user ID
     *
     * @param  int  $userId
     * @return LoyaltyAccount
     */
    public function findOrCreateByUserId(int $userId): LoyaltyAccount;

    /**
     * Find loyalty account by user ID
     *
     * @param  int  $userId
     * @return LoyaltyAccount|null
     */
    public function findByUserId(int $userId): ?LoyaltyAccount;
}

