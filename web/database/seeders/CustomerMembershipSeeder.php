<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CustomerMembership;
use App\Models\MembershipTier;
use Illuminate\Database\Seeder;

class CustomerMembershipSeeder extends Seeder
{
    public function run(): void
    {
        $tier = MembershipTier::first();
        
        if (!$tier) {
            $this->command->warn('No membership tier found. Please create tiers first.');
            return;
        }

        $users = User::whereIn('id', [1, 2, 3])->get();

        foreach ($users as $user) {
            CustomerMembership::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'tier_id' => $tier->id,
                    'total_spent' => 0,
                    'available_points' => 100,
                    'total_points' => 100,
                ]
            );
        }

        $this->command->info('Created customer memberships for ' . $users->count() . ' users.');
    }
}
