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

    protected function makeAdmin(): User
    {
        Role::query()->firstOrCreate(['id' => 1], ['name' => 'Admin']);

        return User::factory()->create([
            'role_id' => 1,
        ]);
    }
}
