<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProofUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_upload_login_and_logout_proofs_and_gets_clear_size_error(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Aplikasi Premium',
            'slug' => 'aplikasi-premium',
            'fulfillment_type' => 'manual_action',
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'process_type' => 'account',
            'name' => 'Akun Uji',
            'slug' => 'akun-uji-proof',
            'price' => 10000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);
        $transaction = Transaction::create([
            'invoice_number' => 'WIB-PROOF-TEST',
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 10000,
            'target_data' => $user->email,
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'credential_data' => [
                'requires_login_proof' => true,
                'requires_logout_proof' => true,
            ],
            'credential_expires_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->postJson(route('user.history.login-proof', $transaction), [
                'login_proof' => UploadedFile::fake()->image('login.jpg'),
            ])
            ->assertOk()
            ->assertJsonStructure(['url', 'uploaded_label']);

        $this->actingAs($user)
            ->postJson(route('user.history.logout-proof', $transaction), [
                'logout_proof' => UploadedFile::fake()->image('logout.png'),
            ])
            ->assertOk()
            ->assertJsonStructure(['url', 'uploaded_label']);

        $transaction->refresh();
        Storage::disk('public')->assertExists($transaction->login_proof_path);
        Storage::disk('public')->assertExists($transaction->logout_proof_path);

        $this->actingAs($user)
            ->postJson(route('user.history.logout-proof', $transaction), [
                'logout_proof' => UploadedFile::fake()->image('besar.jpg')->size(6000),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('logout_proof')
            ->assertJsonPath('errors.logout_proof.0', 'Bukti logout maksimal 5 MB.');

        $transaction->refresh();
        Storage::disk('public')->assertExists($transaction->logout_proof_path);
    }
}
