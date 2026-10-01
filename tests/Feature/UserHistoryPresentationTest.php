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

    public function test_mobile_order_metadata_and_warehouse_email_are_compact(): void
    {
        $historyView = file_get_contents(resource_path('views/user/history.blade.php'));
        $warehouseView = file_get_contents(resource_path('views/admin/credentials/index.blade.php'));

        $this->assertStringContainsString('.history-transaction-meta {', $historyView);
        $this->assertStringContainsString('.history-modal-card .history-transaction-meta {', $historyView);
        $this->assertStringContainsString('history-transaction-meta-time', $historyView);
        $this->assertStringContainsString('history-transaction-meta-status', $historyView);
        $this->assertStringContainsString('.warehouse-email-value {', $warehouseView);
        $this->assertStringContainsString('white-space: nowrap;', $warehouseView);
        $this->assertStringContainsString('name="data_1" class="warehouse-email-value', $warehouseView);
        $this->assertStringContainsString('warehouse-email-value mt-1', $warehouseView);
        $this->assertStringNotContainsString('mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $cred->data_1 }}', $warehouseView);
    }
}
