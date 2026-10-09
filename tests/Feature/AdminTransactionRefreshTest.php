<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTransactionRefreshTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_refresh_requests_redirect_without_running_provider_http_work(): void
    {
        Role::query()->firstOrCreate([
            'id' => User::ROLE_SUPER_ADMIN,
        ], [
            'name' => 'Super Admin',
        ]);

        $admin = User::factory()->create([
            'role_id' => User::ROLE_SUPER_ADMIN,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.transactions.refresh-provider-statuses'))
            ->assertRedirect()
            ->assertSessionHas('success', fn (string $message) => str_contains($message, 'dijadwalkan'));

        $this->actingAs($admin)
            ->get(route('admin.transactions.index', [
                'refresh_api' => 1,
                'month' => '10',
                'year' => '2026',
            ]))
            ->assertRedirect(route('admin.transactions.index', [
                'month' => '10',
                'year' => '2026',
            ]))
            ->assertSessionHas('success', fn (string $message) => str_contains($message, 'dijadwalkan'));
    }
}
