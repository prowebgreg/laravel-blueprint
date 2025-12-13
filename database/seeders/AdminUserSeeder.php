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
        // SECURITY: Only allow in non-production environments
        if (! \in_array(app()->environment(), ['local', 'development', 'testing'], true)) {
            $this->command->warn('AdminUserSeeder is disabled in production environments.');

            return;
        }

        $password = config('auth.default_admin_password');

        if (empty($password)) {
            $this->command->error('DEFAULT_ADMIN_PASSWORD environment variable is not set in auth config.');

            return;
        }

        User::query()->firstOrCreate(
            ['email' => 'info@proweb.ai'],
            [
                'name' => 'Admin',
                'password' => $password, // Will be auto-hashed by the 'hashed' cast
                'email_verified_at' => now(),
            ]
        );
    }
}
