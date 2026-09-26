<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class AdminReportedErrorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_renders_active_products_without_an_undefined_product_type(): void
    {
        $admin = $this->makeUser(User::ROLE_SUPER_ADMIN, 'Admin');
        $category = Category::query()->create([
            'name' => 'Aplikasi Premium',
            'slug' => 'aplikasi-premium',
            'fulfillment_type' => 'manual_account',
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'process_type' => 'account',
            'name' => 'Produk Uji Aktif',
            'slug' => 'produk-uji-aktif',
            'price' => 15000,
            'is_active' => true,
            'status' => 'active',
        ]);
        $product->load('category');
        $products = new LengthAwarePaginator(collect([$product]), 1, 20, 1, [
            'path' => route('admin.products.index'),
        ]);
        $productSections = collect([[
            'name' => $category->name,
            'count' => 1,
            'categories' => collect([[
                'name' => $category->name,
                'count' => 1,
                'products' => collect([$product]),
            ]]),
        ]]);

        $this->actingAs($admin)
            ->view('admin.products.index', [
                'products' => $products,
                'productSections' => $productSections,
                'productFilter' => 'all',
            ])
            ->assertSee('Produk Uji Aktif');
    }

    public function test_dashboard_excel_export_reads_manual_refund_amount_from_metadata(): void
    {
        $admin = $this->makeUser(User::ROLE_SUPER_ADMIN, 'Admin');
        $buyer = $this->makeUser(User::ROLE_BUYER, 'Buyer');
        $category = Category::query()->create([
            'name' => 'Aplikasi Premium',
            'slug' => 'aplikasi-premium',
            'fulfillment_type' => 'manual_account',
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'process_type' => 'account',
            'name' => 'Produk Refund',
            'slug' => 'produk-refund',
            'price' => 20000,
            'is_active' => true,
            'status' => 'active',
        ]);
        $transaction = Transaction::query()->create([
            'invoice_number' => 'WIB-REFUND-TEST',
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'amount' => 20000,
            'paid_amount' => 20000,
            'target_data' => 'buyer@example.test',
            'payment_status' => 'paid',
            'order_status' => 'success',
            'refunded_at' => now(),
        ]);

        Refund::query()->create([
            'transaction_id' => $transaction->id,
            'admin_id' => $admin->id,
            'proof_path' => 'refunds/test.jpg',
            'notes' => json_encode([
                'manual_refund_percent' => 50,
                'manual_refund_amount' => 10000,
            ]),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard.export_excel', [
                'month' => now()->format('m'),
                'year' => now()->format('Y'),
            ]))
            ->assertOk()
            ->assertDownload();
    }

    private function makeUser(int $roleId, string $roleName): User
    {
        Role::query()->firstOrCreate(['id' => $roleId], ['name' => $roleName]);

        return User::factory()->create(['role_id' => $roleId]);
    }
}
