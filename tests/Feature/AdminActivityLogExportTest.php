<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminActivityLogExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_export_uses_the_same_active_filters_as_the_table(): void
    {
        Role::query()->firstOrCreate(['id' => User::ROLE_SUPER_ADMIN], ['name' => 'Admin']);
        Role::query()->firstOrCreate(['id' => User::ROLE_BUYER], ['name' => 'Buyer']);
        $admin = User::factory()->create(['role_id' => User::ROLE_SUPER_ADMIN]);
        $member = User::factory()->create(['role_id' => User::ROLE_BUYER, 'name' => 'Pengguna Oktober']);

        DB::table('activity_logs')->insert([
            [
                'user_id' => $member->id,
                'ip' => '203.0.113.10',
                'method' => 'GET',
                'url' => 'user/dashboard',
                'route_name' => 'user.dashboard',
                'user_agent' => 'Test Browser',
                'created_at' => '2026-10-04 09:00:00',
            ],
            [
                'user_id' => null,
                'ip' => '198.51.100.20',
                'method' => 'GET',
                'url' => 'welcome',
                'route_name' => 'welcome',
                'user_agent' => 'Test Browser',
                'created_at' => '2026-09-04 09:00:00',
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.logs.export_excel', [
            'filter' => 'member',
            'search' => '203.0.113.10',
            'month' => '10',
            'year' => '2026',
        ]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $content = $response->getContent();
        $this->assertStringContainsString('Pengguna Oktober', $content);
        $this->assertStringContainsString('203.0.113.10', $content);
        $this->assertStringNotContainsString('198.51.100.20', $content);
    }
}
