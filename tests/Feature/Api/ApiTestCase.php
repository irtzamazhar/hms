<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function adminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        return $user;
    }

    protected function apiToken(User $user): string
    {
        return $user->createToken('test-token')->plainTextToken;
    }

    protected function asAdmin(): array
    {
        $user  = $this->adminUser();
        $token = $this->apiToken($user);
        return ['Authorization' => "Bearer {$token}"];
    }
}
