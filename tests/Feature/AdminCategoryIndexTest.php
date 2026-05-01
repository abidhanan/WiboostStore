<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_category_index_groups_categories_by_depth(): void
    {
        $admin = $this->makeAdmin();

        $root = Category::create([
            'name' => 'Suntik Sosmed',
            'slug' => 'suntik-sosmed',
            'fulfillment_type' => 'auto_api',
        ]);

        $levelOne = Category::create([
            'parent_id' => $root->id,
            'name' => 'Instagram',
            'slug' => 'sosmed-instagram',
            'fulfillment_type' => 'auto_api',
        ]);

        $levelTwo = Category::create([
            'parent_id' => $levelOne->id,
            'name' => 'Like',
            'slug' => 'sosmed-instagram-like',
            'fulfillment_type' => 'auto_api',
        ]);

        $levelThree = Category::create([
            'parent_id' => $levelTwo->id,
            'name' => 'Like Indonesia',
            'slug' => 'sosmed-instagram-like-indonesia',
            'fulfillment_type' => 'auto_api',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.categories.index'));

        $response->assertOk();
        $response->assertSee('Kategori Utama');
        $response->assertSee('Kategori Turunan 1');
        $response->assertSee('Kategori Turunan 2');
        $response->assertSee('Kategori Turunan 3');
        $response->assertSee($root->name);
        $response->assertSee($levelOne->breadcrumb_name);
        $response->assertSee($levelTwo->breadcrumb_name);
        $response->assertSee($levelThree->breadcrumb_name);
    }

    public function test_admin_category_index_search_matches_parent_branch(): void
    {
        $admin = $this->makeAdmin();

        $root = Category::create([
            'name' => 'Suntik Sosmed',
            'slug' => 'suntik-sosmed',
            'fulfillment_type' => 'auto_api',
        ]);

        $platform = Category::create([
            'parent_id' => $root->id,
            'name' => 'Instagram',
            'slug' => 'sosmed-instagram',
            'fulfillment_type' => 'auto_api',
        ]);

        $metric = Category::create([
            'parent_id' => $platform->id,
            'name' => 'Like',
            'slug' => 'sosmed-instagram-like',
            'fulfillment_type' => 'auto_api',
        ]);

        Category::create([
            'name' => 'Top Up Game',
            'slug' => 'top-up-game',
            'fulfillment_type' => 'auto_api',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.categories.index', [
            'search' => 'instagram',
        ]));

        $response->assertOk();
        $response->assertSee($platform->name);
        $response->assertSee($metric->breadcrumb_name);
        $response->assertDontSee('Top Up Game');
    }

    public function test_admin_can_store_subcategory_and_inherit_parent_fulfillment_type(): void
    {
        $admin = $this->makeAdmin();

        $parent = Category::create([
            'name' => 'Aplikasi Premium',
            'slug' => 'aplikasi-premium',
            'fulfillment_type' => 'stock_based',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'parent_id' => $parent->id,
            'name' => 'Netflix',
            'slug' => 'netflix',
            'description' => 'Akun streaming premium',
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'parent_id' => $parent->id,
            'name' => 'Netflix',
            'slug' => 'netflix',
            'fulfillment_type' => 'stock_based',
        ]);
    }

    public function test_admin_store_treats_repeated_generated_slug_submission_as_success(): void
    {
        $admin = $this->makeAdmin();

        Category::create([
            'name' => 'Suntik Sosmed',
            'slug' => 'suntik-sosmed',
            'fulfillment_type' => 'auto_api',
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), [
                'name' => 'Suntik Sosmed',
                'fulfillment_type' => 'auto_api',
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success', 'Kategori baru berhasil ditambahkan!');
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_admin_store_rejects_slug_used_by_different_category(): void
    {
        $admin = $this->makeAdmin();

        Category::create([
            'name' => 'Top Up Game',
            'slug' => 'top-up-game',
            'fulfillment_type' => 'auto_api',
        ]);

        $response = $this
            ->actingAs($admin)
            ->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), [
                'name' => 'Game Voucher',
                'slug' => 'top-up-game',
                'fulfillment_type' => 'auto_api',
            ]);

        $response->assertRedirect(route('admin.categories.create'));
        $response->assertSessionHasErrors('slug');
        $this->assertDatabaseCount('categories', 1);
    }

    protected function makeAdmin(): User
    {
        Role::query()->firstOrCreate(['id' => 1], ['name' => 'Admin']);

        return User::factory()->create([
            'role_id' => 1,
        ]);
    }
}
