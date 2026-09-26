<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_legal_pages_can_be_rendered(): void
    {
        foreach (['terms', 'privacy-policy', 'refund-policy', 'contact'] as $page) {
            $this->get(route('legal.show', $page))
                ->assertOk()
                ->assertSee('Wiboost Store');
        }
    }

    public function test_legacy_public_urls_redirect_to_their_current_pages(): void
    {
        $this->get('/client/privacy-policy')->assertRedirect('/legal/privacy-policy');
        $this->get('/client/register')->assertRedirect('/register');
        $this->get('/contact')->assertRedirect('/legal/contact');
        $this->get('/contact-us')->assertRedirect('/legal/contact');
        $this->get('/about')->assertRedirect('/');
        $this->get('/team')->assertRedirect('/');
    }

    public function test_public_metadata_endpoints_are_available(): void
    {
        $this->get('/site.webmanifest?v=20260920-seo')
            ->assertOk()
            ->assertHeader('content-type', 'application/manifest+json; charset=UTF-8')
            ->assertJsonPath('name', 'Wiboost Store')
            ->assertJsonPath('start_url', '/');

        $this->get('/meta.json')
            ->assertOk()
            ->assertJsonPath('name', 'Wiboost Store');

        $this->get('/app-ads.txt')
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8');

        $this->get('/sellers.json')
            ->assertOk()
            ->assertJsonPath('version', '1.0')
            ->assertJsonPath('sellers', []);

        $this->get('/dsrdelete.json')
            ->assertOk()
            ->assertJsonPath('status', 'not_configured');
    }

    public function test_user_entry_redirects_guests_and_signed_in_users(): void
    {
        $this->get('/user')->assertRedirect('/login');

        Role::query()->firstOrCreate(['id' => User::ROLE_BUYER], ['name' => 'Buyer']);
        $buyer = User::factory()->create(['role_id' => User::ROLE_BUYER]);
        $this->actingAs($buyer)->get('/user')->assertRedirect(route('user.dashboard'));

        Role::query()->firstOrCreate(['id' => User::ROLE_SUPER_ADMIN], ['name' => 'Admin']);
        $admin = User::factory()->create(['role_id' => User::ROLE_SUPER_ADMIN]);
        $this->actingAs($admin)->get('/user')->assertRedirect(route('admin.dashboard'));
    }
}
