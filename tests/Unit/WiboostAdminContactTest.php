<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\WiboostAdminContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WiboostAdminContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_url_uses_configured_admin_whatsapp_number(): void
    {
        config([
            'wiboost.admin_contact.whatsapp' => '085326513324',
            'wiboost.admin_contact.report_intro' => 'Halo Admin',
        ]);

        $this->get('/login');

        $url = WiboostAdminContact::reportUrl();

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://wa.me/6285326513324?text=', $url);
        $this->assertStringContainsString(rawurlencode('Halo Admin'), $url);
    }

    public function test_report_url_falls_back_to_admin_account_contact_when_env_number_missing(): void
    {
        config([
            'wiboost.admin_contact.whatsapp' => null,
            'wiboost.admin_contact.report_intro' => 'Halo Admin',
        ]);

        DB::table('roles')->insert([
            'id' => 1,
            'name' => 'Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::factory()->create([
            'role_id' => 1,
            'whatsapp' => '081234567890',
        ]);

        $this->get('/login');

        $url = WiboostAdminContact::reportUrl();

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $url);
    }
}
