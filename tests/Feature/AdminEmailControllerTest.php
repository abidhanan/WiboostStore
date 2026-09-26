<?php

namespace Tests\Feature;

use App\Jobs\SendUserBroadcastEmail;
use App\Mail\UserBroadcastMail;
use App\Models\EmailCampaign;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminEmailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_email_management_and_create_pages(): void
    {
        $admin = $this->userWithRole(User::ROLE_MARKETING, 'Marketing');
        $this->userWithRole(User::ROLE_BUYER, 'Buyer');

        $this->actingAs($admin)
            ->get(route('admin.emails.index'))
            ->assertOk()
            ->assertSee('Manajemen Email')
            ->assertSee('Kirim informasi kepada pengguna.')
            ->assertSee('Kirim Email')
            ->assertSee('Belum ada riwayat email.');

        $this->actingAs($admin)
            ->get(route('admin.emails.create'))
            ->assertOk()
            ->assertSee('Pilih Penerima')
            ->assertSee('1 penerima')
            ->assertDontSee('Pratinjau Email')
            ->assertDontSee('Pratinjau Tombol Email');
    }

    public function test_email_is_queued_only_for_customer_scope(): void
    {
        Queue::fake();

        $admin = $this->userWithRole(User::ROLE_SUPER_ADMIN, 'Super Admin');
        $buyer = $this->userWithRole(User::ROLE_BUYER, 'Buyer');
        $reseller = $this->userWithRole(User::ROLE_RESELLER, 'Reseller');

        $this->actingAs($admin)
            ->post(route('admin.emails.store'), [
                'recipient_scope' => 'all',
                'submission_token' => (string) Str::uuid(),
                'subject' => 'Informasi Wiboost',
                'body' => 'Ini adalah informasi terbaru untuk pengguna.',
                'action_label' => 'Buka Wiboost',
                'action_url' => 'https://wiboostore.com',
            ])
            ->assertRedirect(route('admin.emails.index'))
            ->assertSessionHas('success');

        Queue::assertPushed(SendUserBroadcastEmail::class, 2);
        Queue::assertPushed(SendUserBroadcastEmail::class, fn (SendUserBroadcastEmail $job) => $job->recipientEmail === $buyer->email);
        Queue::assertPushed(SendUserBroadcastEmail::class, fn (SendUserBroadcastEmail $job) => $job->recipientEmail === $reseller->email);
        Queue::assertNotPushed(SendUserBroadcastEmail::class, fn (SendUserBroadcastEmail $job) => $job->recipientEmail === $admin->email);

        $campaign = EmailCampaign::query()->sole();
        $this->assertSame(2, $campaign->recipient_count);
        $this->assertSame('Informasi Wiboost', $campaign->subject);
        $this->assertSame('queued', $campaign->status);
    }

    public function test_broadcast_email_template_renders_personalized_content(): void
    {
        $html = (new UserBroadcastMail(
            recipientName: 'Budi',
            subjectText: 'Promo Akhir Pekan',
            bodyText: "Diskon khusus untuk kamu.\nJangan sampai terlewat.",
            actionLabel: 'Belanja Sekarang',
            actionUrl: 'https://wiboostore.com',
        ))->render();

        $this->assertStringContainsString('Halo Budi', $html);
        $this->assertStringContainsString('Promo Akhir Pekan', $html);
        $this->assertStringContainsString('Belanja Sekarang', $html);
    }

    public function test_same_submission_token_does_not_queue_duplicate_email(): void
    {
        Queue::fake();

        $admin = $this->userWithRole(User::ROLE_SUPER_ADMIN, 'Super Admin');
        $this->userWithRole(User::ROLE_BUYER, 'Buyer');
        $token = (string) Str::uuid();
        $payload = [
            'recipient_scope' => 'all',
            'submission_token' => $token,
            'subject' => 'Informasi Wiboost',
            'body' => 'Pesan untuk pelanggan.',
        ];

        $this->actingAs($admin)->post(route('admin.emails.store'), $payload)->assertSessionHas('success');
        $this->actingAs($admin)->post(route('admin.emails.store'), $payload)->assertSessionHas('success');

        Queue::assertPushed(SendUserBroadcastEmail::class, 1);
        $this->assertSame(1, EmailCampaign::query()->count());
    }

    private function userWithRole(int $roleId, string $roleName): User
    {
        Role::query()->firstOrCreate(['id' => $roleId], ['name' => $roleName]);

        return User::factory()->create(['role_id' => $roleId]);
    }
}
