<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Create (or refresh) the account used to reach the admin area.
     *
     * Credentials come from ADMIN_EMAIL / ADMIN_PASSWORD when set; otherwise a
     * random password is generated and printed once.
     */
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@codesparkph.com');
        $name = (string) env('ADMIN_NAME', 'CodeSpark PH');
        $password = (string) env('ADMIN_PASSWORD', '') ?: Str::password(16);

        $user = User::firstOrNew(['email' => $email]);
        $existed = $user->exists;

        $user->name = $name;
        $user->password = $password;
        // Admin routes sit behind the `verified` middleware.
        $user->email_verified_at ??= now();
        $user->save();

        $this->command?->info(($existed ? 'Updated' : 'Created')." admin user: {$email}");
        $this->command?->info("Password: {$password}");
        $this->command?->warn('Change this password after your first login.');
    }
}
