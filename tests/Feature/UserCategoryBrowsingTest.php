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

    public function test_guest_can_browse_catalog_and_product_selection_goes_to_login(): void
    {
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

        $this->get(route('guest.order.category', $root->slug))
            ->assertOk()
            ->assertSee('Masuk')
            ->assertSee(route('guest.order.category', $leaf->slug));

        $this->get(route('guest.order.category', $leaf->slug))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('lg:grid-cols-4', false)
            ->assertSee('wiboost-product-square', false)
            ->assertSee('wiboost-header-logo-frame', false)
            ->assertDontSee('>' . $product->description . '<', false)
            ->assertSee('data-description="' . $product->description . '"', false)
            ->assertSee('data-id="' . $product->id . '"', false)
            ->assertSee("window.location.href='" . route('login') . "'", false);

        $packetRoot = Category::create([
            'name' => 'Paket Data',
            'slug' => 'paket-data',
            'description' => 'Kategori paket data',
            'fulfillment_type' => 'auto_api',
        ]);

        $packetLeaf = Category::create([
            'parent_id' => $packetRoot->id,
            'name' => 'Telkomsel',
            'slug' => 'paket-data-telkomsel',
            'description' => 'Kategori operator',
            'fulfillment_type' => 'auto_api',
        ]);

        $packetProduct = Product::create([
            'category_id' => $packetLeaf->id,
            'provider_source' => 'digiflazz',
            'provider_product_id' => 'TSEL10',
            'process_type' => 'api',
            'name' => 'Telkomsel Internet 10 GB',
            'slug' => 'telkomsel-internet-10-gb',
            'description' => 'Internet aktif 30 hari.',
            'price' => 45000,
            'status' => 'active',
            'is_active' => true,
            'stock_reminder' => 0,
        ]);

        $this->get(route('guest.order.category', $packetLeaf->slug))
            ->assertOk()
            ->assertSee($packetProduct->name)
            ->assertDontSee('>' . $packetProduct->description . '<', false)
            ->assertSee('data-description="' . $packetProduct->description . '"', false);
    }

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
        $rootResponse->assertDontSee('Lihat subkategori');

        $platformResponse = $this->actingAs($user)->get(route('user.order.category', $platform->slug));

        $platformResponse->assertOk();
        $platformResponse->assertSee($metric->name);
        $platformResponse->assertSee(route('user.order.category', $metric->slug));
        $platformResponse->assertDontSee('Lihat subkategori');

        $metricResponse = $this->actingAs($user)->get(route('user.order.category', $metric->slug));

        $metricResponse->assertOk();
        $metricResponse->assertSee($region->name);
        $metricResponse->assertDontSee('layanan aktif');
    }

    public function test_guest_is_redirected_to_login_after_suntik_sosmed_service_level(): void
    {
        $root = Category::create([
            'name' => 'Suntik Sosmed',
            'slug' => 'suntik-sosmed',
            'description' => 'Kategori utama',
            'fulfillment_type' => 'auto_api',
        ]);

        $platform = Category::create([
            'parent_id' => $root->id,
            'name' => 'Instagram',
            'slug' => 'instagram',
            'description' => 'Kategori platform',
            'fulfillment_type' => 'auto_api',
        ]);

        $service = Category::create([
            'parent_id' => $platform->id,
            'name' => 'Followers',
            'slug' => 'instagram-followers',
            'description' => 'Kategori layanan',
            'fulfillment_type' => 'auto_api',
        ]);

        $region = Category::create([
            'parent_id' => $service->id,
            'name' => 'Global',
            'slug' => 'instagram-followers-global',
            'description' => 'Kategori wilayah',
            'fulfillment_type' => 'auto_api',
        ]);

        $this->get(route('guest.order.category', $root->slug))->assertOk();
        $this->get(route('guest.order.category', $platform->slug))->assertOk();
        $this->get(route('guest.order.category', $service->slug))->assertOk();

        $this->get(route('guest.order.category', $region->slug))
            ->assertRedirect(route('login'))
            ->assertSessionHas('url.intended', route('guest.order.category', $region->slug));
    }
}
