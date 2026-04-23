<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCategoryBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_nested_category_branch_is_browsable_even_without_direct_products(): void
    {
        $user = User::factory()->create();

        $root = Category::create([
            'name' => 'Suntik Sosmed',
            'slug' => 'suntik-sosmed',
            'description' => 'Kategori utama',
            'fulfillment_type' => 'auto_api',
        ]);

        $platform = Category::create([
            'parent_id' => $root->id,
            'name' => 'Instagram',
            'slug' => 'sosmed-instagram',
            'description' => 'Kategori platform',
            'fulfillment_type' => 'auto_api',
        ]);

        $metric = Category::create([
            'parent_id' => $platform->id,
            'name' => 'Like',
            'slug' => 'sosmed-instagram-like',
            'description' => 'Kategori metric',
            'fulfillment_type' => 'auto_api',
        ]);

        $region = Category::create([
            'parent_id' => $metric->id,
            'name' => 'Like Indonesia',
            'slug' => 'sosmed-instagram-like-indonesia',
            'description' => 'Kategori region',
            'fulfillment_type' => 'auto_api',
        ]);

        Product::create([
            'category_id' => $region->id,
            'provider_id' => 'ordersosmed',
            'provider_source' => 'ordersosmed',
            'provider_product_id' => 'service-1',
            'process_type' => 'api',
            'name' => 'Instagram Like Indonesia',
            'slug' => 'instagram-like-indonesia',
            'description' => 'Produk test',
            'price' => 10000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $rootResponse = $this->actingAs($user)->get(route('user.order.category', $root->slug));

        $rootResponse->assertOk();
        $rootResponse->assertSee($platform->name);
        $rootResponse->assertSee(route('user.order.category', $platform->slug));
        $rootResponse->assertSee('Lihat subkategori');

        $platformResponse = $this->actingAs($user)->get(route('user.order.category', $platform->slug));

        $platformResponse->assertOk();
        $platformResponse->assertSee($metric->name);
        $platformResponse->assertSee(route('user.order.category', $metric->slug));
        $platformResponse->assertSee('Lihat subkategori');

        $metricResponse = $this->actingAs($user)->get(route('user.order.category', $metric->slug));

        $metricResponse->assertOk();
        $metricResponse->assertSee($region->name);
        $metricResponse->assertSee('1 layanan aktif');
    }
}
