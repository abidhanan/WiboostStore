<?php

namespace Tests\Feature;

use App\Models\User;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class AdminUiConsistencyTest extends TestCase
{
    public function test_every_admin_paginator_uses_the_shared_transaction_pagination_view(): void
    {
        $pagination = file_get_contents(resource_path('views/vendor/pagination/tailwind.blade.php'));

        $this->assertStringContainsString('wiboost-pagination-wrap', $pagination);
        $this->assertStringContainsString('wiboost-pagination-active', $pagination);

        foreach ($this->adminBladeViews() as $path => $contents) {
            if (! str_contains($contents, '->links(')) {
                continue;
            }

            preg_match_all('/->links\s*\(([^)]*)\)/', $contents, $matches);

            foreach ($matches[1] as $arguments) {
                $arguments = trim($arguments);

                $this->assertTrue(
                    $arguments === '' || str_contains($arguments, 'vendor.pagination.tailwind'),
                    "Pagination khusus ditemukan di {$path}; semua halaman admin harus memakai desain transaksi."
                );
            }
        }
    }

    public function test_activity_log_table_keeps_combined_data_in_separate_columns(): void
    {
        $contents = file_get_contents(resource_path('views/admin/logs/index.blade.php'));

        foreach (['Nama', 'Email', 'IP', 'Status IP', 'Metode', 'URL', 'Route', 'Lokasi', 'Jaringan'] as $heading) {
            $this->assertStringContainsString(">{$heading}</th>", $contents);
        }

        $this->assertStringNotContainsString('Lokasi &amp; Jaringan</th>', $contents);
        $this->assertStringNotContainsString('>Aktivitas</th>', $contents);
        $this->assertStringNotContainsString('>Detail</th>', $contents);
        $this->assertStringContainsString('@click="detailData = @js($detailArr); detailOpen = true"', $contents);
        $this->assertStringContainsString('@admin-detail-closed.window="detailOpen = false"', $contents);
        $this->assertStringContainsString('flex w-full flex-col gap-2 sm:flex-row', $contents);
        $this->assertStringContainsString('width: 340px;', $contents);
        $this->assertStringContainsString('inline-block whitespace-nowrap', $contents);
    }

    public function test_deposit_names_are_kept_on_one_line(): void
    {
        $contents = file_get_contents(resource_path('views/admin/deposits/index.blade.php'));

        $this->assertStringContainsString('min-width: 280px;', $contents);
        $this->assertStringContainsString('whitespace-nowrap font-black', $contents);
        $this->assertStringNotContainsString('text-ellipsis whitespace-nowrap font-black', $contents);
    }

    public function test_commission_reseller_names_are_one_line_without_clipping(): void
    {
        $contents = file_get_contents(resource_path('views/admin/commissions/index.blade.php'));

        $this->assertStringContainsString('min-width:340px;', $contents);
        $this->assertStringContainsString('whitespace-nowrap text-sm font-black', $contents);
        $this->assertStringNotContainsString('whitespace-normal break-words text-sm font-black', $contents);
    }

    public function test_legacy_public_urls_do_not_raise_framework_errors(): void
    {
        $this->get('/about-us')->assertRedirect('/');
        $this->get('/user/checkout/process')->assertRedirect(route('login'));
    }

    public function test_scanner_and_expected_authorization_errors_are_filtered_from_discord_alerts(): void
    {
        $contents = file_get_contents(base_path('bootstrap/app.php'));

        foreach (['@vite/client', 'rest/settings', 'livewire/update', 'magento_version'] as $path) {
            $this->assertStringContainsString("'{$path}'", $contents);
        }

        $this->assertStringContainsString('if ($statusCode === 403)', $contents);
    }

    public function test_report_statuses_use_handled_language_on_report_and_dashboard_views(): void
    {
        $reportView = file_get_contents(resource_path('views/admin/reports/index.blade.php'));
        $dashboardView = file_get_contents(resource_path('views/admin/dashboard.blade.php'));

        $this->assertStringContainsString("'Sudah Ditangani'", $reportView);
        $this->assertStringContainsString("'Belum Ditangani'", $reportView);
        $this->assertStringContainsString("'Sudah Ditangani'", $dashboardView);
        $this->assertStringContainsString("'Belum Ditangani'", $dashboardView);
        $this->assertStringNotContainsString("? 'Selesai' : 'Baru'", $dashboardView);
    }

    public function test_otp_page_places_search_first_and_spaces_request_columns(): void
    {
        $contents = file_get_contents(resource_path('views/admin/otp/index.blade.php'));

        $this->assertStringContainsString('order-1 mb-6', $contents);
        $this->assertStringContainsString('order-2 mb-6', $contents);
        $this->assertStringContainsString('Kontak Admin', $contents);
        $this->assertStringContainsString('>Admin OTP</h2>', $contents);
        $this->assertStringContainsString('title="Tambah Nomor"', $contents);
        $this->assertStringContainsString('>Tambah Nomor</span>', $contents);
        $this->assertStringContainsString('bg-[#5a76c8]', $contents);
        $this->assertStringContainsString('min-width:2100px;table-layout:fixed;', $contents);
        $this->assertStringContainsString('<colgroup>', $contents);
        $this->assertStringContainsString('<col style="width:360px">', $contents);
        $this->assertStringContainsString('grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 3rem;', $contents);
        $this->assertStringContainsString('style="align-self: center;" title="Hapus Nomor"', $contents);
    }

    public function test_latest_admin_tables_and_details_follow_the_shared_patterns(): void
    {
        $dashboard = file_get_contents(resource_path('views/admin/dashboard.blade.php'));
        $transactions = file_get_contents(resource_path('views/admin/transactions/index.blade.php'));
        $durations = file_get_contents(resource_path('views/admin/durations/index.blade.php'));
        $users = file_get_contents(resource_path('views/admin/users/index.blade.php'));
        $userEdit = file_get_contents(resource_path('views/admin/users/edit.blade.php'));
        $roleSelect = file_get_contents(resource_path('views/admin/users/partials/role-select.blade.php'));
        $adminLayout = file_get_contents(resource_path('views/layouts/admin.blade.php'));

        $this->assertStringContainsString('Ringkasan OTP', $dashboard);
        $this->assertMatchesRegularExpression('/<section class="[^"]*xl:grid-cols-2[^"]*">[\s\S]*?Ringkasan OTP/', $dashboard);
        $this->assertMatchesRegularExpression('/Produk Terlaris[\s\S]*?grid-cols-1[\s\S]*?>Terjual<[\s\S]*?>Omzet</', $dashboard);
        $this->assertStringNotContainsString('Kelola Produk', $dashboard);
        $this->assertMatchesRegularExpression('/Ringkasan OTP[\s\S]*?min-w-\[1080px\]/', $dashboard);
        $this->assertStringContainsString('min-w-[13rem] whitespace-nowrap px-4 py-3', $dashboard);
        $this->assertStringContainsString('min-w-[20rem] whitespace-nowrap px-4 py-3', $dashboard);
        $this->assertStringNotContainsString('max-w-[10rem] truncate whitespace-nowrap text-xs font-black text-[#2b3a67] xl:text-sm">{{ $otpRequest->user', $dashboard);
        $this->assertStringNotContainsString('max-w-[13rem] truncate whitespace-nowrap text-xs font-black text-[#5a76c8] xl:text-sm', $dashboard);
        $this->assertMatchesRegularExpression('/commissions\.index[\s\S]*?dashboard-view-all-mobile[\s\S]*?inline-flex w-full/', $dashboard);
        $this->assertMatchesRegularExpression('/withdrawals\.index[\s\S]*?dashboard-view-all-mobile[\s\S]*?inline-flex w-full/', $dashboard);
        $this->assertMatchesRegularExpression('/Ringkasan Durasi[\s\S]*?>Habis<\/th>/', $dashboard);
        $this->assertDoesNotMatchRegularExpression('/Ringkasan Stok[\s\S]*?<th[^>]*>Batas<\/th>/', $dashboard);
        $this->assertGreaterThan(strpos($dashboard, 'Ringkasan Penarikan'), strpos($dashboard, 'Ringkasan OTP'));
        $this->assertGreaterThanOrEqual(9, substr_count($dashboard, 'dashboard-view-all-desktop'));
        $this->assertGreaterThanOrEqual(9, substr_count($dashboard, 'dashboard-view-all-mobile'));
        $this->assertStringContainsString('.wiboost-admin-dashboard .dashboard-view-all-desktop', $dashboard);
        $this->assertStringContainsString('display: inline-flex !important;', $dashboard);
        $this->assertMatchesRegularExpression('/Ringkasan Stok[\s\S]*?dashboard-view-all-desktop[\s\S]*?<table[\s\S]*?dashboard-view-all-mobile/', $dashboard);
        $this->assertStringContainsString('whitespace-nowrap px-5 py-4', $transactions);
        $this->assertStringContainsString('>Data Terkait</p>', $durations);
        $this->assertStringContainsString('>Role</p>', $users);
        $this->assertStringContainsString('>Role</label>', $userEdit);
        $this->assertStringContainsString("'9' => 'Sales'", $roleSelect);
        $this->assertStringContainsString('.wiboost-table-scroll-bottom', $adminLayout);
        $this->assertStringContainsString('.wiboost-table-bottom-bar', $adminLayout);
        $this->assertStringContainsString('attachBottomBar(wrapper)', $adminLayout);
        $this->assertStringContainsString('const shouldShow = isVisible && maxScroll > 1;', $adminLayout);
        $this->assertStringContainsString("bar.style.display = shouldShow ? 'block' : 'none'", $adminLayout);
        $this->assertStringContainsString('body.wiboost-admin-shell .admin-theme-select-option.is-selected', $adminLayout);
        $this->assertStringContainsString('body.wiboost-admin-shell .admin-theme-select-option:not(.is-selected)', $adminLayout);
        $this->assertMatchesRegularExpression('/admin-theme-select-option\.is-selected\s*\{[^}]*background:\s*#5a76c8;[^}]*color:\s*#ffffff;/s', $adminLayout);
        $this->assertStringContainsString("item.setAttribute('aria-selected', isSelected ? 'true' : 'false')", $adminLayout);
        $this->assertStringContainsString('id="wiboost-dropdown-option-contrast"', $adminLayout);
        $this->assertStringContainsString('background: #ffffff !important;', $adminLayout);
        $this->assertStringContainsString("const selectedValue = String(input.value ?? '')", $adminLayout);
        $this->assertStringContainsString('.admin-theme-select-option[aria-selected="true"]', $adminLayout);
        $this->assertSame(9, User::ROLE_SALES);
        $this->assertContains(User::ROLE_SALES, User::ADMIN_ROLE_IDS);

        $detailAssets = file_get_contents(resource_path('views/admin/partials/detail-modal-assets.blade.php'));
        $this->assertStringContainsString("backdrop.dataset.adminDetailBackdrop = 'true'", $detailAssets);
        $this->assertStringContainsString("document.addEventListener('pointerdown'", $detailAssets);
        $this->assertStringContainsString("new CustomEvent('admin-detail-closed'", $detailAssets);
        $this->assertStringContainsString('[data-admin-fit-link]', $detailAssets);

        $products = file_get_contents(resource_path('views/admin/products/index.blade.php'));
        $forms = file_get_contents(resource_path('views/admin/forms/index.blade.php'));
        $formShow = file_get_contents(resource_path('views/admin/forms/show.blade.php'));
        $logs = file_get_contents(resource_path('views/admin/logs/index.blade.php'));
        $warehouse = file_get_contents(resource_path('views/admin/credentials/index.blade.php'));

        $this->assertStringContainsString('>Hapus Produk</button>', $products);
        $this->assertStringContainsString('>Penyedia</th>', $products);
        $this->assertStringContainsString('>ID Produk Penyedia</th>', $products);
        $this->assertStringContainsString('overflow-wrap: anywhere;', $products);
        $this->assertStringContainsString("'ordersosmed' => 'Order Sosmed'", $products);
        $this->assertStringContainsString("'digiflazz' => 'DigiFlazz'", $products);
        $this->assertStringContainsString("? ' x' . (\$product->provider_quantity ?? 1)", $products);
        $this->assertStringContainsString('whitespace-nowrap text-xs font-black text-[#8faaf3] drop-shadow-sm sm:text-sm', $products);
        $this->assertStringContainsString('font-size: clamp(0.74rem, 2vw, 0.86rem)', $warehouse);
        $this->assertStringContainsString("old('otp_admin_whatsapp', \$editingValue('otp_admin_whatsapp', ''))", $warehouse);
        $this->assertStringContainsString('x-bind:required="needsOtp"', $warehouse);
        $this->assertStringContainsString("route('admin.credentials.update'", $warehouse);
        $this->assertStringContainsString('title="Edit Data"', $warehouse);
        $this->assertStringContainsString("{{ \$isEditing ? 'Edit Stok' : 'Tambah Stok' }}", $warehouse);
        $this->assertStringContainsString("{{ \$isEditing ? 'Simpan Perubahan' : 'Simpan Data' }}", $warehouse);
        $this->assertStringContainsString('Batal Edit', $warehouse);
        $this->assertStringNotContainsString('title="Batal Edit"', $warehouse);
        $this->assertStringNotContainsString('inline-edit-form', $warehouse);
        $this->assertStringContainsString('lg:sticky lg:top-6', $warehouse);
        $this->assertStringNotContainsString('$cred->otp_admin_name', $warehouse);
        $this->assertStringContainsString('setAdminDropdownOptionState', $adminLayout);
        $this->assertStringContainsString("item.style.setProperty('background', isSelected ? '#5a76c8' : '#ffffff', 'important')", $adminLayout);
        $this->assertStringContainsString('data-input-name="month"', $logs);
        $this->assertStringContainsString('data-input-name="year"', $logs);
        $this->assertStringContainsString("route('admin.logs.export_excel'", $logs);
        $this->assertStringContainsString('Cetak Laporan', $logs);
        $this->assertStringContainsString('px-3 py-3 text-sm font-black text-[#5a76c8] shadow-inner sm:px-4', $formShow);
        $this->assertStringContainsString('position: static !important;', $userEdit);
        $this->assertStringNotContainsString('data-fit-minimum-size="7"', $forms);
        $this->assertStringContainsString('overflow-x-auto whitespace-nowrap text-sm font-black', $forms);
        $this->assertStringContainsString("\$form->benefit_enabled ? 'Ada' : 'Tidak Ada'", $forms);
        $this->assertStringContainsString('admin-detail-link-no-scrollbar', $forms);
        $this->assertStringContainsString("['No', 'Invoice', 'Waktu Transaksi', 'Nama Pelanggan', 'Email', 'Produk', 'Harga', 'Target', 'Metode', 'Bukti Masuk', 'Status']", file_get_contents(app_path('Http/Controllers/Admin/TransactionController.php')));
        $this->assertStringContainsString("['Operasional', 'Permintaan OTP'", file_get_contents(app_path('Http/Controllers/Admin/DashboardController.php')));
    }

    /** @return array<string, string> */
    private function adminBladeViews(): array
    {
        $views = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(resource_path('views/admin'), RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $path = $file->getPathname();
            $views[$path] = (string) file_get_contents($path);
        }

        return $views;
    }
}
