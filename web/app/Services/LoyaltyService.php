<?php

namespace App\Services;

use App\Repositories\Contracts\LoyaltyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LoyaltyService
{
    public function __construct(
        private LoyaltyRepositoryInterface $loyaltyRepository
    ) {
    }

    /**
     * Get loyalty account with transactions
     *
     * @param  int  $userId
     * @return array
     */
    public function getAccount(int $userId): array
    {
        $account = $this->loyaltyRepository->findOrCreateByUserId($userId);

        $account->load(['transactions' => function ($q) {
            $q->latest()->limit(50);
        }]);

        return $account->toArray();
    }
}

