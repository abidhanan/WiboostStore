<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Deposit;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminReportRecordOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_record_picker_uses_history_status_and_payment_labels(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $root = Category::create([
            'name' => 'Top Up Game',
            'slug' => 'top-up-game',
            'description' => 'Kategori utama',
            'fulfillment_type' => 'auto_api',
        ]);

        $leaf = Category::create([
            'parent_id' => $root->id,
            'name' => 'Mobile Legends',
            'slug' => 'mobile-legends',
            'description' => 'Kategori produk',
            'fulfillment_type' => 'auto_api',
        ]);

        $product = Product::create([
            'category_id' => $leaf->id,
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'ML86',
            'process_type' => 'api',
            'name' => 'Mobile Legends 86 Diamonds',
            'slug' => 'mobile-legends-86-diamonds',
            'description' => 'Produk test',
            'price' => 20000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $createdAt = Carbon::create(2026, 5, 11, 5, 12);

        $transaction = Transaction::create([
            'invoice_number' => 'WIB-HELP-001',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 10000,
            'paid_amount' => 10150,
            'target_data' => '12345678',
            'payment_status' => 'paid',
            'order_status' => 'processing',
            'payment_method' => 'tripay',
        ]);

        $transaction->timestamps = false;
        $transaction->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        $this->actingAs($user)
            ->get(route('user.report.admin', ['type' => 'game']))
            ->assertOk()
            ->assertSee('WIB-HELP-001')
            ->assertSee('11 MEI 2026, 05:12 - QRIS - PROSES')
            ->assertSee('proof-preview', false)
            ->assertSee('proof-remove', false)
            ->assertSee('report-record-list', false)
            ->assertDontSee('Belum ada gambar dipilih')
            ->assertDontSee('TRIPAY')
            ->assertDontSee('PROCESSING');

        $response = $this->actingAs($user)->post(route('user.report.admin.store'), [
            'category_key' => 'game',
            'record_id' => 'transaction-' . $transaction->id,
            'issue' => 'Nominal pesanan perlu diperiksa.',
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        parse_str((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $message = (string) ($query['text'] ?? '');

        $this->assertStringContainsString('Total Bayar: Rp 10.150', $message);
        $this->assertStringContainsString('Metode: QRIS', $message);
        $this->assertStringContainsString('Status Pesanan: PROSES', $message);
        $this->assertStringNotContainsString('ID Provider:', $message);
    }

    public function test_deposit_record_picker_uses_indonesian_date_without_status_and_whatsapp_matches(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $createdAt = Carbon::create(2026, 5, 11, 5, 12);

        $deposit = Deposit::create([
            'invoice_number' => 'DEP-HELP-001',
            'user_id' => $user->id,
            'amount' => 50000,
            'paid_amount' => 50150,
            'payment_status' => 'paid',
            'payment_method' => 'QRISREALTIME',
            'tripay_reference' => null,
        ]);

        $deposit->timestamps = false;
        $deposit->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        $this->assertSame('QRIS', $deposit->display_payment_method);

        $this->actingAs($user)
            ->get(route('user.report.admin', ['type' => 'deposit']))
            ->assertOk()
            ->assertSee('Pilih Riwayat Deposit')
            ->assertSee('DEP-HELP-001')
            ->assertSee('11 MEI 2026, 05:12')
            ->assertDontSee('QRISREALTIME')
            ->assertDontSee('PAID');

        $response = $this->actingAs($user)->post(route('user.report.admin.store'), [
            'category_key' => 'deposit',
            'record_id' => 'deposit-' . $deposit->id,
            'issue' => 'Saldo belum masuk ke akun saya.',
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertRedirect();

        parse_str((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $message = (string) ($query['text'] ?? '');

        $this->assertStringContainsString('Metode: QRIS', $message);
        $this->assertStringContainsString('Waktu: 11 MEI 2026, 05:12', $message);
        $this->assertStringNotContainsString('Status:', $message);
        $this->assertStringNotContainsString('QRISREALTIME', $message);
        $this->assertStringNotContainsString('Referensi QRIS:', $message);
    }

    public function test_order_report_whatsapp_message_uses_requested_input_buyer_block(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'Abid Hanan',
            'email' => 'abidhanan0904@gmail.com',
            'whatsapp' => '6281234567890',
        ]);

        $root = Category::create([
            'name' => 'Suntik Sosmed',
            'slug' => 'suntik-sosmed',
            'description' => 'Kategori utama',
            'fulfillment_type' => 'auto_api',
        ]);

        $leaf = Category::create([
            'parent_id' => $root->id,
            'name' => 'Instagram',
            'slug' => 'suntik-sosmed-instagram',
            'description' => 'Kategori produk',
            'fulfillment_type' => 'auto_api',
        ]);

        $product = Product::create([
            'category_id' => $leaf->id,
            'provider_id' => 'ordersosmed',
            'provider_source' => 'ordersosmed',
            'provider_product_id' => '894771',
            'process_type' => 'api',
            'name' => 'Instagram Followers',
            'slug' => 'instagram-followers',
            'description' => 'Produk test',
            'price' => 2452,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $createdAt = Carbon::create(2026, 6, 12, 6, 45);

        $transaction = Transaction::create([
            'invoice_number' => 'WIB-PZCOKBDRSTJZ',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 2452,
            'paid_amount' => 2452,
            'target_data' => 'wiboost.store',
            'order_input_data' => [
                'fields' => [
                    [
                        'name' => 'target_data',
                        'label' => 'Username akun / link postingan',
                        'value' => 'wiboost.store',
                        'target_summary' => true,
                    ],
                    [
                        'name' => 'order_quantity',
                        'label' => 'Jumlah Pesanan',
                        'value' => '620',
                    ],
                ],
            ],
            'response_data' => [
                'provider_order_id' => '894771',
            ],
            'payment_status' => 'paid',
            'order_status' => 'success',
            'payment_method' => 'wallet',
        ]);

        $transaction->timestamps = false;
        $transaction->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        $response = $this->actingAs($user)->post(route('user.report.admin.store'), [
            'category_key' => 'sosmed',
            'record_id' => 'transaction-' . $transaction->id,
            'issue' => 'Cekkk',
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertRedirect();
        $response->assertCookie('wiboost_after_admin_report', 'dashboard');

        parse_str((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $message = (string) ($query['text'] ?? '');

        $expected = implode("\n", [
            'Jenis Laporan: Suntik Sosmed',
            'Nama: Abid Hanan',
            'Email: abidhanan0904@gmail.com',
            'WhatsApp: 6281234567890',
            '',
            'Kendala:',
            'Cekkk',
            '',
            'Bukti Kendala:',
        ]);

        $this->assertStringContainsString($expected, $message);
        $this->assertStringContainsString(implode("\n", [
            'Data Pesanan:',
            'Invoice: WIB-PZCOKBDRSTJZ',
            'Kategori: Suntik Sosmed',
            'Produk: Instagram Followers',
            'Total Bayar: Rp 2.452',
            'Metode: SALDO',
            'Status Pesanan: SUKSES',
            'ID Provider: 894771',
            'Waktu: 12 JUN 2026, 06:45',
            '',
            'Input Pembeli:',
            'Username akun / link postingan: wiboost.store',
            'Jumlah Pesanan: 620',
            '',
            'Mohon dibantu pengecekannya. Terima kasih.',
        ]), $message);
    }
}
