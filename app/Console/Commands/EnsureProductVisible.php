<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Support\WiboostCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnsureProductVisible extends Command
{
    protected const GROK_LOGO_PATH = 'products/grok-private-logo.svg';

    protected $signature = 'wiboost:ensure-product-visible
        {name=Grok Private 1 Minggu : Nama produk yang dipastikan tampil}
        {--create-missing : Buat ulang produk bila tidak ada di database}
        {--price=0 : Harga produk bila harus dibuat ulang}
        {--membership-price= : Harga member bila harus dibuat ulang}
        {--category-slug= : Paksa kategori existing berdasarkan slug bila harus dibuat ulang}
        {--dry-run : Preview tanpa mengubah database}';

    protected $description = 'Pastikan produk tertentu tetap aktif dan punya kategori existing supaya muncul di halaman produk/katalog.';

    protected Collection $categories;

    public function handle(): int
    {
        $name = trim((string) $this->argument('name'));
        $dryRun = (bool) $this->option('dry-run');

        if ($name === '') {
            $this->error('Nama produk wajib diisi.');

            return self::FAILURE;
        }

        $this->categories = Category::query()
            ->with(['parent.parent.parent.parent', 'children'])
            ->withCount('children')
            ->get();

        $products = Product::query()
            ->with('category.parent.parent.parent.parent')
            ->where(function ($query) use ($name) {
                $query->where('name', $name)
                    ->orWhere('name', 'like', '%' . $name . '%');

                if (Str::contains(Str::lower($name), 'grok')) {
                    $query->orWhere(function ($innerQuery) {
                        $innerQuery->where('name', 'like', '%Grok%')
                            ->where('name', 'like', '%Private%')
                            ->where('name', 'like', '%1 Minggu%');
                    });
                }
            })
            ->orderBy('id')
            ->get();

        if ($products->isEmpty() && (bool) $this->option('create-missing')) {
            $created = $this->createMissingProduct($name, $dryRun);

            if ($created) {
                $products = collect([$created]);
            }
        }

        if ($products->isEmpty()) {
            $this->warn("Produk '{$name}' tidak ditemukan di database.");
            $this->warn('Tambahkan --create-missing bila produk memang terhapus dan harus dibuat ulang.');

            return self::SUCCESS;
        }

        $rows = [];

        DB::transaction(function () use ($products, $dryRun, &$rows) {
            foreach ($products as $product) {
                $targetCategory = $this->visibleCategoryFor($product);
                $updates = [];

                if ((bool) $product->is_active !== true) {
                    $updates['is_active'] = true;
                }

                if ((string) $product->status !== 'active') {
                    $updates['status'] = 'active';
                }

                if (! in_array((string) $product->process_type, WiboostCatalog::activeProductProcessTypes(), true)) {
                    $updates['process_type'] = 'account';
                }

                if ($targetCategory && (int) $product->category_id !== (int) $targetCategory->id) {
                    $updates['category_id'] = $targetCategory->id;
                }

                if ($this->isGrokProduct($product) && blank($product->image)) {
                    $updates['image'] = self::GROK_LOGO_PATH;
                }

                if ($updates !== [] && ! $dryRun) {
                    $updates['updated_at'] = now();
                    $product->forceFill($updates)->saveQuietly();
                    $product->refresh()->load('category.parent.parent.parent.parent');
                }

                $rows[] = [
                    $product->id,
                    Str::limit($product->name, 44),
                    $dryRun ? 'Preview' : ($updates === [] ? 'Sudah OK' : 'Diperbaiki'),
                    $product->is_active ? 'aktif' : 'nonaktif',
                    $product->status ?: '-',
                    $product->process_type ?: '-',
                    $product->category?->breadcrumb_name ?: ($targetCategory?->breadcrumb_name ?: '-'),
                ];
            }
        });

        $this->info($dryRun ? 'Mode preview: belum ada data diubah.' : 'Produk dicek dan diperbaiki agar tampil.');
        $this->table(['ID', 'Produk', 'Status Fix', 'Aktif', 'Status', 'Tipe', 'Kategori'], $rows);

        return self::SUCCESS;
    }

    protected function visibleCategoryFor(Product $product): ?Category
    {
        $current = $product->category_id
            ? $this->categories->first(fn (Category $category) => (int) $category->id === (int) $product->category_id)
            : null;

        if ($current && (int) ($current->children_count ?? 0) === 0 && $this->isInPremiumCatalog($current)) {
            return $current;
        }

        return $this->guessCategoryFor($product) ?: $current;
    }

    protected function guessCategoryFor(Product $product): ?Category
    {
        $productText = $this->normalizeText(implode(' ', array_filter([
            $product->name,
            $product->provider_product_id,
            $product->description,
        ])));

        $strictCategory = $this->categories
            ->filter(function (Category $category) use ($productText) {
                $breadcrumb = $this->normalizeText($category->breadcrumb_name . ' ' . $category->slug);
                $ownText = $this->normalizeText($category->name . ' ' . $category->slug);

                if (! $this->isPremiumBreadcrumb($breadcrumb)) {
                    return false;
                }

                if (str_contains($productText, ' grok ') && str_contains($ownText, ' grok ')) {
                    return true;
                }

                return str_contains($ownText, ' ai ')
                    || str_contains($ownText, ' umum ')
                    || str_contains($ownText, ' lainnya ')
                    || str_contains($ownText, ' tools ')
                    || str_contains($ownText, ' premium ');
            })
            ->sortByDesc(function (Category $category) use ($productText) {
                $ownText = $this->normalizeText($category->name . ' ' . $category->slug);
                $score = (int) $category->depth * 20;

                if (($category->children_count ?? 0) === 0) {
                    $score += 120;
                }

                if (str_contains($productText, ' grok ') && str_contains($ownText, ' grok ')) {
                    $score += 500;
                }

                if (filled($category->image) || filled($category->emote)) {
                    $score += 30;
                }

                return $score;
            })
            ->first();

        if ($strictCategory) {
            return $strictCategory;
        }

        return $this->categories
            ->filter(function (Category $category) {
                $breadcrumb = $this->normalizeText($category->breadcrumb_name . ' ' . $category->slug);

                return $this->isPremiumBreadcrumb($breadcrumb);
            })
            ->sortByDesc(function (Category $category) {
                $ownText = $this->normalizeText($category->name . ' ' . $category->slug);
                $score = (int) $category->depth * 20;

                if (($category->children_count ?? 0) === 0) {
                    $score += 120;
                }

                if (str_contains($ownText, ' umum ') || str_contains($ownText, ' lainnya ')) {
                    $score += 60;
                }

                if (filled($category->image) || filled($category->emote)) {
                    $score += 30;
                }

                return $score;
            })
            ->first();
    }

    protected function createMissingProduct(string $name, bool $dryRun): ?Product
    {
        $reference = Product::query()
            ->with('category.parent.parent.parent.parent')
            ->where('name', 'like', '%Grok%')
            ->orderByRaw("CASE WHEN name LIKE '%Private%' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN name LIKE '%Minggu%' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->first();

        $category = $this->forcedCategory()
            ?: $reference?->category
            ?: $this->guessCategoryFor(new Product(['name' => $name, 'description' => '']));

        if ($category && (int) (($this->categories->first(fn (Category $item) => (int) $item->id === (int) $category->id)?->children_count) ?? 0) > 0) {
            $category = $this->guessCategoryFor(new Product(['name' => $name, 'description' => ''])) ?: $category;
        }

        if (! $category) {
            $this->error('Produk hilang dan kategori Aplikasi Premium existing tidak ditemukan. Produk tidak dibuat agar katalog tidak makin acak.');

            return null;
        }

        $payload = [
            'category_id' => $category->id,
            'process_type' => 'account',
            'name' => $name,
            'description' => $reference?->description ?: 'Produk aplikasi premium Grok Private 1 Minggu.',
            'price' => (float) $this->option('price'),
            'membership_price' => filled($this->option('membership-price')) ? (float) $this->option('membership-price') : null,
            'discount_percent' => 0,
            'provider_id' => null,
            'provider_source' => 'manual',
            'provider_product_id' => 'manual-grok-private-1-minggu',
            'provider_quantity' => 1,
            'target_label' => null,
            'target_placeholder' => null,
            'target_hint' => null,
            'requires_buyer_email' => false,
            'stock_reminder' => 0,
            'image' => $reference?->image ?: self::GROK_LOGO_PATH,
            'emote' => $reference?->emote,
            'is_active' => true,
            'status' => 'active',
        ];

        if ($reference) {
            foreach ([
                'price',
                'membership_price',
                'discount_percent',
                'emote',
                'requires_buyer_email',
                'stock_reminder',
            ] as $field) {
                if (filled($reference->{$field})) {
                    $payload[$field] = $reference->{$field};
                }
            }
        }

        if ($dryRun) {
            $product = new Product($payload);
            $product->id = 0;
            $product->setRelation('category', $category);

            return $product;
        }

        $product = Product::create($payload);
        $product->load('category.parent.parent.parent.parent');

        $this->info("Produk '{$name}' dibuat ulang di kategori: " . $category->breadcrumb_name);

        return $product;
    }

    protected function forcedCategory(): ?Category
    {
        $slug = trim((string) $this->option('category-slug'));

        if ($slug === '') {
            return null;
        }

        return $this->categories
            ->first(fn (Category $category) => (string) $category->slug === $slug);
    }

    protected function isGrokProduct(Product $product): bool
    {
        return str_contains($this->normalizeText($product->name . ' ' . $product->provider_product_id), ' grok ');
    }

    protected function isInPremiumCatalog(Category $category): bool
    {
        return $this->isPremiumBreadcrumb($this->normalizeText($category->breadcrumb_name . ' ' . $category->slug));
    }

    protected function isPremiumBreadcrumb(string $breadcrumb): bool
    {
        return str_contains($breadcrumb, ' aplikasi premium ')
            || str_contains($breadcrumb, ' aplikasi-premium ');
    }

    protected function normalizeText(?string $text): string
    {
        $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = Str::lower($text);
        $text = preg_replace('/[^\pL\pN.]+/u', ' ', $text) ?? $text;

        return ' ' . trim(preg_replace('/\s+/u', ' ', $text) ?? $text) . ' ';
    }
}
