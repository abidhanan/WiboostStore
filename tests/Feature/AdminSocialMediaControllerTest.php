<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SocialMediaStat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSocialMediaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_social_media_page(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->get(route('admin.social-media.index'))
            ->assertOk()
            ->assertSee('Manajemen Media Sosial')
            ->assertSee('Instagram')
            ->assertSee('TikTok')
            ->assertSee('Total Postingan')
            ->assertSee('Total Followers')
            ->assertSee('Jangkauan')
            ->assertSee('Kunjungan Profil')
            ->assertSee('Klik Link Bio')
            ->assertSee('Link Postingan Teramai')
            ->assertSee('Link Postingan Tersepi')
            ->assertSee('Cari kategori, bulan, tahun, atau link postingan...')
            ->assertSee('Cetak Laporan')
            ->assertSee('data-input-name="year"', false)
            ->assertSee('Tambah Data')
            ->assertDontSee('Kelola Data');

        $this->actingAs($admin)
            ->get(route('admin.social-media.create', ['platform' => 'tiktok']))
            ->assertOk()
            ->assertSee('Tambah Data')
            ->assertSee('Bulan')
            ->assertSee('Tahun');
    }

    public function test_admin_can_create_and_update_monthly_platform_stats(): void
    {
        $admin = $this->adminUser();
        $payload = [
            'platform' => 'instagram',
            'month' => 10,
            'year' => 2026,
            'total_posts' => 120,
            'total_followers' => 15000,
            'reach' => 83000,
            'profile_visits' => 6400,
            'bio_link_clicks' => 510,
            'top_post_url' => 'https://www.instagram.com/p/ramai',
            'low_post_url' => 'https://www.instagram.com/p/sepi',
        ];

        $this->actingAs($admin)
            ->post(route('admin.social-media.store'), $payload)
            ->assertRedirect(route('admin.social-media.index', ['platform' => 'instagram']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('social_media_stats', [
            'platform' => 'instagram',
            'total_followers' => 15000,
            'updated_by' => $admin->id,
        ]);

        $stat = SocialMediaStat::query()->sole();
        $payload['total_followers'] = 16000;
        $this->actingAs($admin)
            ->put(route('admin.social-media.update', $stat), $payload)
            ->assertSessionHas('success');

        $this->assertSame(1, SocialMediaStat::query()->count());
        $this->assertSame(16000, SocialMediaStat::query()->sole()->total_followers);
    }

    public function test_admin_can_delete_social_media_stats(): void
    {
        $admin = $this->adminUser();
        $stat = SocialMediaStat::query()->create([
            'platform' => 'instagram',
            'month' => 10,
            'year' => 2026,
            'total_posts' => 12,
            'total_followers' => 3400,
            'reach' => 5600,
            'profile_visits' => 780,
            'bio_link_clicks' => 90,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.social-media.destroy', $stat), [
                'return_to' => route('admin.social-media.index', ['platform' => 'instagram']),
            ])
            ->assertRedirect(route('admin.social-media.index', ['platform' => 'instagram']))
            ->assertSessionHas('success', 'Data media sosial berhasil dihapus.');

        $this->assertDatabaseMissing('social_media_stats', ['id' => $stat->id]);
    }

    public function test_social_media_forms_use_themed_dropdowns_and_polished_table_actions(): void
    {
        $admin = $this->adminUser();
        SocialMediaStat::query()->create([
            'platform' => 'tiktok',
            'month' => 10,
            'year' => 2026,
            'total_posts' => 5,
            'total_followers' => 2500,
            'reach' => 4800,
            'profile_visits' => 320,
            'bio_link_clicks' => 27,
            'top_post_url' => 'https://www.tiktok.com/@wiboost/video/1',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.social-media.index'))
            ->assertOk()
            ->assertSee('decoration-current', false)
            ->assertSee('Detail Media Sosial')
            ->assertDontSee('Lihat Detail')
            ->assertSee('admin-social-detail-', false)
            ->assertSee('openAdminUniversalDetail', false)
            ->assertSee('Hapus Data')
            ->assertSee('whitespace-nowrap', false);

        $view = file_get_contents(resource_path('views/admin/social-media/index.blade.php'));
        $detailSource = strstr($view, 'Detail Media Sosial');
        $this->assertStringContainsString('Hapus Data</button>', $detailSource);
        $this->assertStringNotContainsString("['name' => 'trash'", strstr($detailSource, '</form>', true));

        $this->actingAs($admin)
            ->get(route('admin.social-media.create'))
            ->assertOk()
            ->assertSee('data-admin-theme-select', false)
            ->assertSee('admin-theme-select-arrow', false)
            ->assertDontSee('Kategori dan Waktu')
            ->assertDontSee('Statistik Media Sosial')
            ->assertDontSee('>Periode<', false)
            ->assertDontSee('>Performa<', false);
    }

    public function test_platform_filter_only_displays_selected_category(): void
    {
        $admin = $this->adminUser();

        SocialMediaStat::query()->create([
            'platform' => 'instagram',
            'month' => 10,
            'year' => 2026,
            'total_posts' => 11,
            'total_followers' => 1111,
            'reach' => 2222,
            'profile_visits' => 333,
            'bio_link_clicks' => 44,
        ]);
        SocialMediaStat::query()->create([
            'platform' => 'tiktok',
            'month' => 10,
            'year' => 2026,
            'total_posts' => 22,
            'total_followers' => 9999,
            'reach' => 8888,
            'profile_visits' => 777,
            'bio_link_clicks' => 66,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.social-media.index', ['platform' => 'instagram']))
            ->assertOk()
            ->assertSee('1.111')
            ->assertDontSee('9.999');
    }

    public function test_social_media_urls_must_be_valid(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->from(route('admin.social-media.index'))
            ->post(route('admin.social-media.store'), [
                'platform' => 'tiktok',
                'month' => 10,
                'year' => 2026,
                'total_posts' => 0,
                'total_followers' => 0,
                'reach' => 0,
                'profile_visits' => 0,
                'bio_link_clicks' => 0,
                'top_post_url' => 'bukan-link',
            ])
            ->assertRedirect(route('admin.social-media.index'))
            ->assertSessionHasErrors('top_post_url');

        $this->assertDatabaseCount('social_media_stats', 0);
    }

    public function test_search_finds_month_and_rejects_duplicate_platform_period(): void
    {
        $admin = $this->adminUser();
        $payload = [
            'platform' => 'instagram',
            'month' => 10,
            'year' => 2026,
            'total_posts' => 1,
            'total_followers' => 2,
            'reach' => 3,
            'profile_visits' => 4,
            'bio_link_clicks' => 5,
        ];

        $this->actingAs($admin)->post(route('admin.social-media.store'), $payload)->assertSessionHas('success');

        $this->actingAs($admin)
            ->get(route('admin.social-media.index', ['search' => 'Oktober']))
            ->assertOk()
            ->assertSee('Oktober 2026');

        $this->actingAs($admin)
            ->from(route('admin.social-media.create'))
            ->post(route('admin.social-media.store'), $payload)
            ->assertRedirect(route('admin.social-media.create'))
            ->assertSessionHasErrors('platform');

        $this->assertDatabaseCount('social_media_stats', 1);
    }

    public function test_year_filter_pagination_and_excel_export_follow_active_filters(): void
    {
        $admin = $this->adminUser();

        foreach (range(1, 21) as $index) {
            SocialMediaStat::query()->create([
                'platform' => $index <= 12 ? 'instagram' : 'tiktok',
                'month' => $index <= 12 ? $index : $index - 12,
                'year' => 2026,
                'total_posts' => $index,
                'total_followers' => 1000 + $index,
                'reach' => 2000 + $index,
                'profile_visits' => 300 + $index,
                'bio_link_clicks' => 40 + $index,
            ]);
        }

        SocialMediaStat::query()->create([
            'platform' => 'instagram',
            'month' => 1,
            'year' => 2025,
            'total_posts' => 999,
            'total_followers' => 9999,
            'reach' => 9999,
            'profile_visits' => 999,
            'bio_link_clicks' => 99,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.social-media.index', ['year' => 2026]))
            ->assertOk()
            ->assertSee('wiboost-pagination-wrap', false)
            ->assertSee('page=2', false)
            ->assertDontSee('9.999');

        $export = $this->actingAs($admin)->get(route('admin.social-media.export_excel', [
            'platform' => 'instagram',
            'year' => 2026,
        ]));

        $export->assertOk();
        $this->assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $export->headers->get('Content-Type')
        );
        $this->assertStringContainsString('laporan-media-sosial-2026-instagram.xlsx', (string) $export->headers->get('Content-Disposition'));
    }

    private function adminUser(): User
    {
        Role::query()->firstOrCreate(
            ['id' => User::ROLE_MARKETING],
            ['name' => 'Marketing'],
        );

        return User::factory()->create(['role_id' => User::ROLE_MARKETING]);
    }
}
