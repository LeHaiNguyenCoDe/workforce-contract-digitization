<?php

namespace App\Repositories;

use App\Models\LoyaltyAccount;
use App\Repositories\Contracts\LoyaltyRepositoryInterface;

class LoyaltyRepository implements LoyaltyRepositoryInterface
{
    /**
     * Find or create loyalty account by user ID
     *
     * @param  int  $userId
     * @return LoyaltyAccount
     */
    public function findOrCreateByUserId(int $userId): LoyaltyAccount
    {
        return LoyaltyAccount::firstOrCreate(
            ['user_id' => $userId],
            ['points' => 0]
        );
    }

    /**
     * Find loyalty account by user ID
     *
     * @param  int  $userId
     * @return LoyaltyAccount|null
     */
    public function findByUserId(int $userId): ?LoyaltyAccount
    {
        return LoyaltyAccount::where('user_id', $userId)->first();
    }
}

