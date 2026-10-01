<?php

namespace Tests\Feature;

use Tests\TestCase;

class UserHistoryPresentationTest extends TestCase
{
    public function test_warehouse_optional_hint_uses_regular_weight(): void
    {
        $view = file_get_contents(resource_path('views/admin/credentials/index.blade.php'));

        $this->assertStringContainsString('Semua form di bawah opsional.', $view);
        $this->assertStringContainsString('font-normal', $view);
        $this->assertStringNotContainsString('<span class="font-black underline">opsional</span>', $view);
    }

    public function test_order_detail_has_compact_otp_action_and_single_line_email(): void
    {
        $view = file_get_contents(resource_path('views/user/history.blade.php'));

        $this->assertStringContainsString('.history-credential-value {', $view);
        $this->assertStringContainsString('history-credential-value--email', $view);
        $this->assertStringContainsString('white-space: nowrap;', $view);
        $this->assertStringContainsString('history-credential-value min-w-0', $view);
        $this->assertStringContainsString("{{ \$key === 'email' ? 'history-credential-value--email' : 'break-all' }}", $view);
        $this->assertStringContainsString('Minta OTP', $view);
        $this->assertStringNotContainsString('Minta OTP via WhatsApp', $view);
        $this->assertStringNotContainsString('Silahkan cek tutorial untuk login atau minta bantuan OTP ke admin.', $view);
    }
}
