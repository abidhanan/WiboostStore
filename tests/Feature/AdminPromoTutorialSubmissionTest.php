<?php

namespace Tests\Feature;

use App\Models\Promo;
use App\Models\Role;
use App\Models\Tutorial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPromoTutorialSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_promo_create_token_prevents_duplicate_submit(): void
    {
        Role::firstOrCreate(['id' => 1], ['name' => 'Admin']);

        $admin = User::factory()->create(['role_id' => 1]);
        $token = 'promo-token-demo';

        $payload = [
            'submission_token' => $token,
            'badge_text' => 'INFO PROMO',
            'title' => 'Promo Test',
            'description' => 'Promo khusus testing.',
            'emoji' => '*',
            'theme' => 'blue',
            'is_active' => '1',
        ];

        $this->actingAs($admin)
            ->withSession(['admin_promo_create_tokens' => [$token => true]])
            ->post(route('admin.promos.store'), $payload)
            ->assertRedirect(route('admin.promos.index'));

        $this->actingAs($admin)
            ->post(route('admin.promos.store'), $payload)
            ->assertRedirect(route('admin.promos.index'));

        $this->assertSame(1, Promo::where('title', 'Promo Test')->count());
    }

    public function test_tutorial_create_token_prevents_duplicate_submit(): void
    {
        Role::firstOrCreate(['id' => 1], ['name' => 'Admin']);

        $admin = User::factory()->create(['role_id' => 1]);
        $token = 'tutorial-token-demo';

        $payload = [
            'submission_token' => $token,
            'title' => 'Tutorial Test',
            'category' => 'Aplikasi Premium',
            'description' => 'Panduan khusus testing.',
            'icon' => '*',
            'youtube_url' => null,
            'content' => 'Isi tutorial.',
            'is_active' => '1',
        ];

        $this->actingAs($admin)
            ->withSession(['admin_tutorial_create_tokens' => [$token => true]])
            ->post(route('admin.tutorials.store'), $payload)
            ->assertRedirect(route('admin.tutorials.index'));

        $this->actingAs($admin)
            ->post(route('admin.tutorials.store'), $payload)
            ->assertRedirect(route('admin.tutorials.index'));

        $this->assertSame(1, Tutorial::where('title', 'Tutorial Test')->count());
    }
}
