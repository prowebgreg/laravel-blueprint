<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'info@proweb.ai'],
            ['name' => 'Admin']
        );

        // Only set password on initial creation to avoid re-hashing on subsequent runs
        if ($user->wasRecentlyCreated) {
            $user->password = bcrypt('Levonik2007@');
            $user->email_verified_at = now();
            $user->save();
        }
    }
}
