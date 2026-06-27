<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates (or promotes) a PLATFORM admin: a cross-tenant vendor account with
 * no hospital_id and the super_admin role, able to use the tenant console.
 *
 *   php artisan tenant:platform-admin owner@vendor.com --name="Platform Owner"
 */
class CreatePlatformAdmin extends Command
{
    protected $signature = 'tenant:platform-admin {email} {--name=Platform Admin} {--password=}';

    protected $description = 'Create or promote a platform (vendor) super-admin with no tenant';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->option('password') ?: Str::password(16);

        $user = User::withoutGlobalScopes()->firstOrNew(['email' => $email]);
        $user->name = $user->name ?: $this->option('name');
        $user->hospital_id = null;                 // cross-tenant
        $user->user_type = 'super_admin';
        $user->status = 'active';
        $user->email_verified_at = now();
        if (! $user->exists || $this->option('password')) {
            $user->password = Hash::make($password);
        }
        $user->save();

        if (! $user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
        }

        $this->info("Platform admin ready: {$email}");
        if ($this->option('password') || ! $user->wasRecentlyCreated) {
            // password only shown when we generated it for a brand-new account
        }
        if (! $this->option('password') && $user->wasRecentlyCreated) {
            $this->line("Generated password: {$password}");
        }

        return self::SUCCESS;
    }
}
