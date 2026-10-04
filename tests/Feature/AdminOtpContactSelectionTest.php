<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\AdminReport;
use App\Models\OtpAdmin;
use App\Models\OtpRequest;
use App\Models\Product;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Transaction;
use App\Services\OrderFulfillmentService;
use App\Support\WiboostAdminContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class AdminOtpContactSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_page_saves_cs_name_and_no_longer_contains_otp_form(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.dashboard.contact-numbers.update'), [
                'admin_cs_name' => 'Admin Customer Service',
                'admin_cs_whatsapp' => '081234567890',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame('Admin Customer Service', WiboostAdminContact::csName());
        $this->assertSame('6281234567890', WiboostAdminContact::csWhatsappNumber());

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('NAMA ADMIN CUSTOMER SERVICE')
            ->assertSee('NOMOR ADMIN CUSTOMER SERVICE')
            ->assertDontSee('Nomor Admin OTP')
            ->assertDontSee('OTP:');
    }

    public function test_report_status_labels_are_reduced_to_handled_or_unhandled(): void
    {
        $report = new AdminReport(['status' => 'new']);
        $this->assertSame('Belum Ditangani', $report->status_label);

        $report->status = 'read';
        $this->assertSame('Belum Ditangani', $report->status_label);

        $report->status = 'closed';
        $this->assertSame('Sudah Ditangani', $report->status_label);
    }

    public function test_google_avatar_column_accepts_long_provider_urls(): void
    {
        $this->assertSame('text', Schema::getColumnType('users', 'google_avatar'));

        $user = User::factory()->create([
            'google_avatar' => 'https://lh3.googleusercontent.com/a-/' . str_repeat('avatar-segment-', 80),
        ]);

        $this->assertGreaterThan(255, strlen((string) $user->fresh()->google_avatar));
    }

    public function test_admin_can_save_multiple_named_otp_contacts(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.otp.admins.save'), [
                'admins' => [
                    ['name' => 'Ahawi', 'whatsapp' => '081111111111'],
                    ['name' => 'Duran', 'whatsapp' => '628222222222'],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame([
            '6281111111111',
            '628222222222',
        ], WiboostAdminContact::otpWhatsappNumbers());
        $this->assertSame('6281111111111', SiteSetting::valueFor('admin_otp_whatsapp'));
        $this->assertSame('Ahawi', WiboostAdminContact::otpAdminNameForNumber('6281111111111'));

        $this->actingAs($admin)
            ->get(route('admin.otp.index'))
            ->assertOk()
            ->assertSee('Ahawi')
            ->assertSee('628222222222')
            ->assertSee('Cetak Laporan')
            ->assertSee('Simpan Nomor')
            ->assertSee('Manajemen OTP')
            ->assertSee('Kelola admin penerima permintaan OTP pelanggan.')
            ->assertSee('Kontak Admin')
            ->assertSee('Admin OTP')
            ->assertSee('Tambah Nomor')
            ->assertDontSee('Tabel Admin OTP belum tersedia.')
            ->assertDontSee('>STATUS<', false);

        $this->actingAs($admin)
            ->post(route('admin.otp.admins.save'), [
                'admins' => [
                    ['name' => 'Ahawi', 'whatsapp' => '6281111111111'],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('otp_admins', ['name' => 'Duran']);
        $this->assertSame(['6281111111111'], WiboostAdminContact::otpWhatsappNumbers());

        $this->actingAs($admin)
            ->get(route('admin.otp.export_excel', [
                'month' => now()->format('m'),
                'year' => now()->format('Y'),
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_user_otp_click_is_recorded_and_admin_can_handle_it(): void
    {
        config()->set('services.discord.webhooks.otp', 'https://discord.test/api/webhooks/otp');
        Http::fake();

        $admin = $this->makeAdmin();
        Role::query()->firstOrCreate(['id' => User::ROLE_BUYER], ['name' => 'Buyer']);
        $user = User::factory()->create([
            'role_id' => User::ROLE_BUYER,
            'whatsapp' => '628123456789',
        ]);
        $product = $this->makeAccountProduct();
        $transaction = Transaction::create([
            'invoice_number' => 'WIB-OTP-REQUEST-1',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'amount' => 10000,
            'target_data' => $user->email,
            'payment_status' => 'paid',
            'order_status' => 'success',
            'credential_data' => [
                'needs_otp' => true,
                'otp_admin_name' => 'Ahawi',
                'otp_admin_whatsapp' => '628111111111',
            ],
        ]);

        $this->actingAs($user)
            ->post(route('user.history.request-otp', $transaction), [
                'message' => 'Mohon kirim OTP untuk WIB-OTP-REQUEST-1.',
            ])
            ->assertRedirect();

        $otpRequest = OtpRequest::query()->firstOrFail();
        $this->assertSame('Belum Ditangani', $otpRequest->status_label);
        $this->assertSame('Ahawi', $otpRequest->otp_admin_name);
        Http::assertSent(fn ($request) => $request->url() === 'https://discord.test/api/webhooks/otp'
            && data_get($request->data(), 'embeds.0.title') === 'Permintaan OTP Baru'
            && collect(data_get($request->data(), 'embeds.0.fields', []))->contains(
                fn ($field) => data_get($field, 'name') === 'Invoice'
                    && data_get($field, 'value') === 'WIB-OTP-REQUEST-1'
            ));

        $this->actingAs($admin)
            ->get(route('admin.otp.index'))
            ->assertOk()
            ->assertSee('WIB-OTP-REQUEST-1')
            ->assertSee($user->email)
            ->assertSee('Belum Ditangani');

        $this->actingAs($admin)
            ->patch(route('admin.otp.requests.update', $otpRequest), ['status' => 'handled'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Sudah Ditangani', $otpRequest->fresh()->status_label);
        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->url() === 'https://discord.test/api/webhooks/otp'
            && data_get($request->data(), 'embeds.0.title') === 'Permintaan OTP Sudah Ditangani'
            && collect(data_get($request->data(), 'embeds.0.fields', []))->contains(
                fn ($field) => data_get($field, 'name') === 'Status'
                    && data_get($field, 'value') === 'Sudah Ditangani'
            ));

        $this->actingAs($admin)
            ->patch(route('admin.otp.requests.update', $otpRequest), ['status' => 'handled'])
            ->assertSessionHasNoErrors();

        Http::assertSentCount(2);
    }

    public function test_user_otp_click_still_reaches_discord_before_otp_table_is_available(): void
    {
        config()->set('services.discord.webhooks.otp', 'https://discord.test/api/webhooks/otp');
        Http::fake();

        Role::query()->firstOrCreate(['id' => User::ROLE_BUYER], ['name' => 'Buyer']);
        $user = User::factory()->create([
            'role_id' => User::ROLE_BUYER,
            'whatsapp' => '628123456789',
        ]);
        $product = $this->makeAccountProduct();
        $transaction = Transaction::create([
            'invoice_number' => 'WIB-OTP-NO-TABLE-1',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'amount' => 10000,
            'target_data' => $user->email,
            'payment_status' => 'paid',
            'order_status' => 'success',
            'credential_data' => [
                'needs_otp' => true,
                'otp_admin_name' => 'Ahawi',
                'otp_admin_whatsapp' => '628111111111',
            ],
        ]);

        Schema::dropIfExists('otp_requests');

        $this->actingAs($user)
            ->post(route('user.history.request-otp', $transaction), [
                'message' => 'Mohon kirim OTP untuk WIB-OTP-NO-TABLE-1.',
            ])
            ->assertRedirect();

        Http::assertSent(fn ($request) => $request->url() === 'https://discord.test/api/webhooks/otp'
            && data_get($request->data(), 'embeds.0.title') === 'Permintaan OTP Baru'
            && collect(data_get($request->data(), 'embeds.0.fields', []))->contains(
                fn ($field) => data_get($field, 'name') === 'Invoice'
                    && data_get($field, 'value') === 'WIB-OTP-NO-TABLE-1'
            ));
    }

    public function test_warehouse_requires_and_saves_selected_otp_number_when_enabled(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeAccountProduct();
        SiteSetting::setValue('admin_otp_admins', json_encode([
            ['name' => 'Ahawi', 'number' => '628111111111'],
            ['name' => 'Duran', 'number' => '628222222222'],
        ]));

        $this->actingAs($admin)
            ->post(route('admin.credentials.store', $product), [
                'max_usage' => 1,
                'needs_otp' => 1,
            ])
            ->assertSessionHasErrors('otp_admin_whatsapp');

        $storeResponse = $this->actingAs($admin)
            ->post(route('admin.credentials.store', $product), [
                'max_usage' => 1,
                'data_1' => 'stok-baru@example.com',
                'needs_otp' => 1,
                'otp_admin_whatsapp' => '628222222222',
            ]);

        $credential = $product->credentials()->firstOrFail();

        $storeResponse
            ->assertStatus(303)
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success')
            ->assertRedirect(route('admin.credentials.index', [
                'product' => $product,
                'created' => $credential->id,
            ]));

        $this->assertDatabaseHas('product_credentials', [
            'product_id' => $product->id,
            'needs_otp' => 1,
            'otp_admin_whatsapp' => '628222222222',
            'otp_admin_name' => 'Duran',
        ]);

        $payloadMethod = new ReflectionMethod(OrderFulfillmentService::class, 'credentialPayload');
        $credentialPayload = $payloadMethod->invoke(app(OrderFulfillmentService::class), $credential, $product);
        $this->assertSame('628222222222', $credentialPayload['otp_admin_whatsapp']);
        $this->assertSame('Duran', $credentialPayload['otp_admin_name']);

        $this->followRedirects($storeResponse)
            ->assertOk()
            ->assertSee('stok-baru@example.com')
            ->assertSee('data-no-submit-lock="true"', false)
            ->assertSee('name="data_1" value=""', false)
            ->assertSee('name="max_usage" required min="1" value="1"', false)
            ->assertSee('Pilih Nomor Admin OTP')
            ->assertSee('Duran')
            ->assertSee('+628222222222')
            ->assertSee('Edit Data')
            ->assertDontSee('Edit Stok')
            ->assertDontSee('Simpan Perubahan');

        $this->actingAs($admin)
            ->get(route('admin.credentials.index', [
                'product' => $product,
                'edit' => $credential->id,
            ]))
            ->assertOk()
            ->assertSee('Edit Stok')
            ->assertSee('Simpan Perubahan')
            ->assertSee('Batal Edit')
            ->assertSee('stok-baru@example.com')
            ->assertSee('name="editing_credential_id" value="'.$credential->id.'"', false);

        $updateResponse = $this->actingAs($admin)
            ->put(route('admin.credentials.update', $credential), [
                'editing_credential_id' => $credential->id,
                'max_usage' => 2,
                'data_1' => 'stok-diperbarui@example.com',
                'needs_otp' => 1,
                'otp_admin_whatsapp' => '628111111111',
                'requires_login_proof' => 1,
                'requires_logout_proof' => 1,
                'duration_value' => 14,
                'duration_unit' => 'day',
            ]);

        $updateResponse
            ->assertStatus(303)
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success')
            ->assertRedirect(route('admin.credentials.index', [
                'product' => $product,
                'updated' => $credential->id,
            ]));

        $this->assertDatabaseHas('product_credentials', [
            'id' => $credential->id,
            'data_1' => 'stok-diperbarui@example.com',
            'max_usage' => 2,
            'needs_otp' => 1,
            'otp_admin_whatsapp' => '628111111111',
            'otp_admin_name' => 'Ahawi',
            'requires_login_proof' => 1,
            'requires_logout_proof' => 1,
            'duration_value' => 14,
            'duration_unit' => 'day',
        ]);

        $this->followRedirects($updateResponse)
            ->assertOk()
            ->assertSee('stok-diperbarui@example.com')
            ->assertSee('Tambah Stok')
            ->assertSee('name="data_1" value=""', false)
            ->assertDontSee('Edit Stok')
            ->assertDontSee('Batal Edit')
            ->assertDontSee('Simpan Perubahan');

        $secondStoreResponse = $this->actingAs($admin)
            ->post(route('admin.credentials.store', $product), [
                'max_usage' => 1,
                'data_1' => 'stok-kedua@example.com',
            ]);
        $secondCredential = $product->credentials()->latest('id')->firstOrFail();

        $secondStoreResponse
            ->assertStatus(303)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.credentials.index', [
                'product' => $product,
                'created' => $secondCredential->id,
            ]));

        $this->followRedirects($secondStoreResponse)
            ->assertOk()
            ->assertSee('stok-diperbarui@example.com')
            ->assertSee('stok-kedua@example.com')
            ->assertSee('name="data_1" value=""', false)
            ->assertSee('name="max_usage" required min="1" value="1"', false);

        $this->actingAs($admin)
            ->post(route('admin.credentials.store', $product), [
                'max_usage' => 1,
                'data_1' => 'stok-bukti@example.com',
                'requires_login_proof' => 1,
                'requires_logout_proof' => 1,
            ])
            ->assertSessionHasErrors('duration_value');

        $this->assertDatabaseMissing('product_credentials', [
            'data_1' => 'stok-bukti@example.com',
        ]);

        $proofStoreResponse = $this->actingAs($admin)
            ->post(route('admin.credentials.store', $product), [
                'max_usage' => 1,
                'data_1' => 'stok-bukti@example.com',
                'requires_login_proof' => 1,
                'requires_logout_proof' => 1,
                'duration_value' => 30,
                'duration_unit' => 'day',
            ]);

        $proofStoreResponse
            ->assertStatus(303)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('product_credentials', [
            'product_id' => $product->id,
            'data_1' => 'stok-bukti@example.com',
            'requires_login_proof' => 1,
            'requires_logout_proof' => 1,
            'duration_value' => 30,
            'duration_unit' => 'day',
        ]);
        $this->assertDatabaseCount('product_credentials', 3);

        $warehouseView = file_get_contents(resource_path('views/admin/credentials/index.blade.php'));
        $this->assertStringContainsString('x-show="requiresLogoutProof"', $warehouseView);
        $this->assertStringContainsString('Wajib diisi apabila Unggah Bukti Keluar diaktifkan.', $warehouseView);
        $this->assertStringContainsString('bg-[#ffe5e5] text-[#ff4d4d]', $warehouseView);
        $this->assertStringNotContainsString('Durasi {{ $cred->duration_value }}', $warehouseView);

        $this->actingAs($admin)
            ->delete(route('admin.credentials.destroy', $credential))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('product_credentials', ['id' => $credential->id]);
    }

    public function test_warehouse_rejects_otp_number_that_is_not_configured(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeAccountProduct();
        SiteSetting::setValue('admin_otp_whatsapps', json_encode(['628111111111']));

        $this->actingAs($admin)
            ->post(route('admin.credentials.store', $product), [
                'max_usage' => 1,
                'needs_otp' => 1,
                'otp_admin_whatsapp' => '628999999999',
            ])
            ->assertSessionHasErrors('otp_admin_whatsapp');

        $this->assertDatabaseCount('product_credentials', 0);
    }

    private function makeAdmin(): User
    {
        Role::query()->firstOrCreate(['id' => User::ROLE_SUPER_ADMIN], ['name' => 'Admin']);

        return User::factory()->create(['role_id' => User::ROLE_SUPER_ADMIN]);
    }

    private function makeAccountProduct(): Product
    {
        $category = Category::create([
            'name' => 'Aplikasi Premium',
            'slug' => 'aplikasi-premium',
            'fulfillment_type' => 'stock_based',
        ]);

        return Product::create([
            'category_id' => $category->id,
            'process_type' => 'account',
            'name' => 'Produk OTP Test',
            'price' => 10000,
            'status' => 'active',
            'is_active' => true,
        ]);
    }
}
