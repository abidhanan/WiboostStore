<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductCategoryMapping
{
    public const PATH = 'wiboost/product-category-mapping.json';

    public const BACKUP_DIRECTORY = 'wiboost/backups';

    public const CATEGORY_DESCRIPTION = 'Pilih kategori di bawah ini untuk lanjut.';

    protected static ?array $cache = null;

    public static function saveFromCurrentProducts(): array
    {
        $mappings = [];
        $productsPayload = [];
        $categorySlugs = [];

        Product::query()
            ->with('category.parent.parent.parent.parent')
            ->whereNotNull('category_id')
            ->orderBy('id')
            ->chunkById(500, function ($products) use (&$mappings, &$productsPayload, &$categorySlugs) {
                foreach ($products as $product) {
                    $category = $product->category;
                    $categorySlug = (string) ($category?->slug ?? '');

                    if ($categorySlug === '') {
                        continue;
                    }

                    foreach (self::keysForProduct($product) as $key) {
                        $mappings[$key] = $categorySlug;
                    }

                    $categorySlugs[$categorySlug] = true;
                    $productsPayload[] = [
                        'id' => (int) $product->id,
                        'name' => (string) $product->name,
                        'provider_source' => $product->provider_source,
                        'provider_id' => $product->provider_id,
                        'provider_product_id' => $product->provider_product_id,
                        'process_type' => $product->process_type,
                        'status' => $product->status,
                        'is_active' => (bool) $product->is_active,
                        'category_id' => (int) $category->id,
                        'category_slug' => $categorySlug,
                        'category_name' => (string) $category->name,
                        'category_breadcrumb' => $category->breadcrumb_name,
                        'root_category_slug' => self::rootSlug($category),
                    ];
                }
            });

        ksort($mappings);
        usort($productsPayload, fn (array $a, array $b) => [$a['category_breadcrumb'], $a['name'], $a['id']] <=> [$b['category_breadcrumb'], $b['name'], $b['id']]);

        $categoriesPayload = Category::query()
            ->with('parent.parent.parent.parent')
            ->whereIn('slug', array_keys($categorySlugs))
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Category $category) => [
                $category->slug => [
                    'id' => (int) $category->id,
                    'name' => (string) $category->name,
                    'slug' => (string) $category->slug,
                    'parent_id' => $category->parent_id ? (int) $category->parent_id : null,
                    'parent_slug' => $category->parent?->slug,
                    'root_slug' => self::rootSlug($category),
                    'depth' => (int) $category->depth,
                    'breadcrumb' => $category->breadcrumb_name,
                    'image' => $category->image,
                    'emote' => $category->emote,
                    'fulfillment_type' => $category->fulfillment_type,
                ],
            ])
            ->sortKeys()
            ->all();

        $generatedAt = now();
        $backupPath = self::backupPath($generatedAt->format('Ymd-His'));

        $payload = [
            'generated_at' => $generatedAt->toIso8601String(),
            'count' => count($mappings),
            'product_count' => count($productsPayload),
            'category_count' => count($categoriesPayload),
            'backup_path' => $backupPath,
            'mappings' => $mappings,
            'categories' => $categoriesPayload,
            'products' => $productsPayload,
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        Storage::disk('local')->put(self::PATH, $json);
        Storage::disk('local')->put($backupPath, $json);
        self::$cache = $payload;

        return $payload;
    }

    public static function categoryFor(string $providerSource, ?string $providerProductId = null, ?string $productName = null): ?Category
    {
        $slug = self::categorySlugFor($providerSource, $providerProductId, $productName);

        if (! $slug) {
            return null;
        }

        return Category::query()
            ->with('parent.parent.parent.parent')
            ->where('slug', $slug)
            ->first();
    }

    public static function categorySlugFor(string $providerSource, ?string $providerProductId = null, ?string $productName = null): ?string
    {
        $mappings = self::mappings();

        if ($mappings === []) {
            return null;
        }

        foreach (self::lookupKeys($providerSource, $providerProductId, $productName) as $key) {
            if (isset($mappings[$key]) && filled($mappings[$key])) {
                return (string) $mappings[$key];
            }
        }

        return null;
    }

    public static function rootSlug(Category $category): string
    {
        $cursor = $category;

        while ($cursor->parent) {
            $cursor = $cursor->parent;
        }

        return (string) $cursor->slug;
    }

    public static function keysForProduct(Product $product): array
    {
        $source = self::normalizeSource((string) ($product->provider_source ?: $product->provider_id ?: $product->process_type ?: 'manual'));

        return self::lookupKeys($source, $product->provider_product_id, $product->name);
    }

    public static function storagePath(): string
    {
        return Storage::disk('local')->path(self::PATH);
    }

    public static function backupStoragePath(?string $backupPath = null): string
    {
        return Storage::disk('local')->path($backupPath ?: self::BACKUP_DIRECTORY);
    }

    protected static function backupPath(string $timestamp): string
    {
        return self::BACKUP_DIRECTORY . "/product-category-mapping-{$timestamp}.json";
    }

    protected static function mappings(): array
    {
        if (self::$cache === null) {
            if (! Storage::disk('local')->exists(self::PATH)) {
                self::$cache = ['mappings' => []];
            } else {
                $payload = json_decode((string) Storage::disk('local')->get(self::PATH), true);
                self::$cache = is_array($payload) ? $payload : ['mappings' => []];
            }
        }

        return is_array(self::$cache['mappings'] ?? null) ? self::$cache['mappings'] : [];
    }

    protected static function lookupKeys(string $providerSource, ?string $providerProductId = null, ?string $productName = null): array
    {
        $source = self::normalizeSource($providerSource);
        $keys = [];
        $sku = self::normalizeValue($providerProductId);
        $name = Str::slug((string) $productName);

        if ($sku !== '') {
            $keys[] = "{$source}:sku:{$sku}";
        }

        if ($name !== '') {
            $keys[] = "{$source}:name:{$name}";
        }

        return array_values(array_unique($keys));
    }

    protected static function normalizeSource(string $source): string
    {
        return Str::slug($source !== '' ? $source : 'manual');
    }

    protected static function normalizeValue(?string $value): string
    {
        return Str::lower(trim((string) $value));
    }
}
