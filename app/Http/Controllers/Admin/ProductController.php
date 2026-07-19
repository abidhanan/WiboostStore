<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Support\WiboostCatalog;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $productFilter = (string) $request->query('product_filter', 'all');
        $allowedProductFilters = ['all', 'api', 'account'];

        if (! in_array($productFilter, $allowedProductFilters, true)) {
            $productFilter = 'all';
        }

        $rootCategorySlug = $this->rootCategorySlugExpression();
        $rootCategoryName = $this->rootCategoryNameExpression();
        $categoryPath = $this->categoryPathExpression();

        $query = Product::query()
            ->select('products.*')
            ->with('category.parent.parent.parent.parent.parent')
            ->leftJoin('categories as product_category', 'products.category_id', '=', 'product_category.id')
            ->leftJoin('categories as parent_category', 'product_category.parent_id', '=', 'parent_category.id')
            ->leftJoin('categories as grandparent_category', 'parent_category.parent_id', '=', 'grandparent_category.id')
            ->leftJoin('categories as great_grandparent_category', 'grandparent_category.parent_id', '=', 'great_grandparent_category.id')
            ->leftJoin('categories as great_great_grandparent_category', 'great_grandparent_category.parent_id', '=', 'great_great_grandparent_category.id');

        $this->hideRemovedCatalogProducts($query);
        $query->whereIn('products.process_type', WiboostCatalog::activeProductProcessTypes());
        $query->where('products.is_active', true)
            ->where('products.status', 'active');

        if ($productFilter !== 'all') {
            $query->where('products.process_type', $productFilter);
        }

        if ($request->filled('search')) {
            $query->where(function ($innerQuery) use ($request) {
                $innerQuery->where('products.name', 'like', '%' . $request->search . '%')
                    ->orWhere('products.provider_product_id', 'like', '%' . $request->search . '%');
            });
        }

        $products = $query
            ->orderByRaw($this->productRootCategoryOrderSql($rootCategorySlug))
            ->orderByRaw($rootCategoryName)
            ->orderByRaw($categoryPath)
            ->orderBy('products.name')
            ->orderByDesc('products.created_at')
            ->paginate(20)
            ->withQueryString();

        $productSections = $products
            ->getCollection()
            ->groupBy(fn (Product $product) => $this->resolveRootCategoryName($product))
            ->sortKeysUsing(fn (string $first, string $second) => $this->compareRootCategoryNames($first, $second))
            ->map(function ($rootProducts, string $rootCategoryName) {
                return [
                    'name' => $rootCategoryName,
                    'count' => $rootProducts->count(),
                    'categories' => $rootProducts
                        ->groupBy(fn (Product $product) => $this->resolveDescendantCategoryName($product))
                        ->sortKeysUsing(fn (string $first, string $second) => strnatcasecmp($first, $second))
                        ->map(function ($categoryProducts, string $categoryName) {
                            return [
                                'name' => $categoryName,
                                'count' => $categoryProducts->count(),
                                'products' => $categoryProducts->values(),
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        return view('admin.products.index', compact('products', 'productSections', 'productFilter'));
    }

    public function create()
    {
        $categories = $this->visibleProductCategories();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);
        $existingProduct = $this->findExistingNonApiProduct($validated);

        if ($existingProduct) {
            return redirect()
                ->route('admin.products.edit', $existingProduct)
                ->with('error', 'Produk dengan nama dan kategori yang sama sudah ada. Kamu diarahkan ke produk yang sudah tersimpan agar tidak dobel.');
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create($this->buildPayload($validated, $request, $imagePath));

        return $this->redirectToReturnTo($request, 'admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = $this->visibleProductCategories();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product->id);
        $imagePath = $product->image;

        if ($request->boolean('remove_image') && $product->image) {
            Storage::disk('public')->delete($product->image);
            $imagePath = null;
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update($this->buildPayload($validated, $request, $imagePath, $product));

        return $this->redirectToReturnTo($request, 'admin.products.index')->with('success', 'Perubahan produk berhasil disimpan.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    public function syncDigiflazz()
    {
        return $this->runSyncCommand('sync:digiflazz', 'Digiflazz');
    }

    public function syncOrderSosmed()
    {
        return $this->runSyncCommand('sync:ordersosmed', 'OrderSosmed', [
            '--match-local-categories' => true,
        ]);
    }

    protected function validateProduct(Request $request, ?int $productId = null): array
    {
        $this->normalizeProviderFieldsForValidation($request);

        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'membership_price' => 'nullable|numeric|min:0',
            'provider_product_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $request->process_type === 'api'),
            ],
            'provider_source' => [
                'nullable',
                'string',
                'in:ordersosmed,digiflazz',
                Rule::requiredIf(fn () => $request->process_type === 'api'),
            ],
            'provider_quantity' => 'nullable|integer|min:1|max:1000000',
            'process_type' => 'required|in:api,account',
            'target_label' => 'nullable|string|max:255',
            'target_placeholder' => 'nullable|string|max:255',
            'target_hint' => 'nullable|string|max:1000',
            'requires_buyer_email' => 'nullable|boolean',
            'stock_reminder' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'emote' => 'nullable|string|max:50',
        ]);
    }

    protected function normalizeProviderFieldsForValidation(Request $request): void
    {
        $providerSource = $request->input('provider_source');

        if ($request->input('process_type') !== 'api' || in_array($providerSource, ['stock', 'local', 'none'], true)) {
            $request->merge([
                'provider_source' => null,
            ]);
        }
    }

    protected function buildPayload(array $validated, Request $request, ?string $imagePath, ?Product $product = null): array
    {
        $requiresTargetInput = $validated['process_type'] === 'api';
        $usesInventory = $validated['process_type'] === 'account';
        $shouldPreserveProvider = $validated['process_type'] !== 'api'
            && $product
            && filled($product->provider_source)
            && filled($product->provider_product_id);

        $providerSource = $validated['process_type'] === 'api'
            ? ($validated['provider_source'] ?? null)
            : ($shouldPreserveProvider ? $product->provider_source : null);

        $providerProductId = $validated['process_type'] === 'api'
            ? $validated['provider_product_id']
            : ($shouldPreserveProvider ? $product->provider_product_id : null);

        $providerQuantity = $validated['process_type'] === 'api'
            ? max(1, (int) ($validated['provider_quantity'] ?? 1))
            : ($shouldPreserveProvider ? max(1, (int) ($product->provider_quantity ?? 1)) : 1);

        return [
            'name' => $validated['name'],
            'slug' => $product?->slug ?: (Str::slug($validated['name']) . '-' . Str::random(5)),
            'category_id' => $validated['category_id'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'membership_price' => $validated['membership_price'] ?? null,
            'provider_product_id' => $providerProductId,
            'provider_source' => $providerSource,
            'provider_id' => $providerSource,
            'provider_quantity' => $providerQuantity,
            'process_type' => $validated['process_type'],
            'target_label' => $requiresTargetInput ? ($validated['target_label'] ?? null) : null,
            'target_placeholder' => $requiresTargetInput ? ($validated['target_placeholder'] ?? null) : null,
            'target_hint' => $requiresTargetInput ? ($validated['target_hint'] ?? null) : null,
            'requires_buyer_email' => $validated['process_type'] === 'account' ? $request->boolean('requires_buyer_email') : false,
            'stock_reminder' => $usesInventory ? ($validated['stock_reminder'] ?? 0) : 0,
            'image' => $imagePath,
            'emote' => $validated['emote'] ?? null,
            'is_active' => true,
            'status' => 'active',
        ];
    }

    protected function findExistingNonApiProduct(array $validated): ?Product
    {
        if (($validated['process_type'] ?? null) === 'api') {
            return null;
        }

        return Product::query()
            ->where('category_id', $validated['category_id'])
            ->whereRaw('LOWER(name) = ?', [Str::lower($validated['name'])])
            ->where(function ($query) {
                $query->whereNull('provider_source')
                    ->orWhere('provider_source', '');
            })
            ->where(function ($query) {
                $query->whereNull('provider_product_id')
                    ->orWhere('provider_product_id', '');
            })
            ->first();
    }

    protected function runSyncCommand(string $command, string $providerLabel, array $parameters = [])
    {
        try {
            $exitCode = Artisan::call($command, $parameters);
            $output = trim((string) Artisan::output());
            $message = $this->shortenOutput($output);

            if ($exitCode === 0) {
                return redirect()->route('admin.products.index')
                    ->with('success', trim("{$providerLabel} berhasil disinkronkan. {$message}"));
            }

            return redirect()->route('admin.products.index')
                ->with('error', trim("{$providerLabel} gagal disinkronkan. {$message}"));
        } catch (\Throwable $e) {
            return redirect()->route('admin.products.index')
                ->with('error', "{$providerLabel} gagal dijalankan: {$e->getMessage()}");
        }
    }

    protected function shortenOutput(string $output): string
    {
        if ($output === '') {
            return '';
        }

        return Str::of($output)
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->limit(220, '...')
            ->value();
    }

    protected function visibleProductCategories()
    {
        $query = Category::with('parent.parent')
            ->where(function ($query) {
                $query->whereNotNull('parent_id')
                    ->orDoesntHave('children');
            })
            ->orderBy('parent_id')
            ->orderBy('name');

        $this->hideRemovedCatalogCategories($query);

        return $query->get();
    }

    protected function resolveRootCategoryName(Product $product): string
    {
        $category = $product->category;

        while ($category?->parent) {
            $category = $category->parent;
        }

        return $category?->name ?: 'Kategori Kosong';
    }

    protected function resolveDescendantCategoryName(Product $product): string
    {
        $category = $product->category;

        if (! $category) {
            return 'Kategori Kosong';
        }

        $segments = [];

        while ($category) {
            array_unshift($segments, $category->name);
            $category = $category->parent;
        }

        array_shift($segments);

        return $segments !== []
            ? implode(' / ', $segments)
            : 'Produk Utama';
    }

    protected function compareRootCategoryNames(string $first, string $second): int
    {
        $firstSlug = Str::slug($first);
        $secondSlug = Str::slug($second);

        if ($firstSlug === 'aplikasi-premium' && $secondSlug !== 'aplikasi-premium') {
            return -1;
        }

        if ($secondSlug === 'aplikasi-premium' && $firstSlug !== 'aplikasi-premium') {
            return 1;
        }

        return strnatcasecmp($first, $second);
    }

    protected function rootCategorySlugExpression(): string
    {
        return "COALESCE(great_great_grandparent_category.slug, great_grandparent_category.slug, grandparent_category.slug, parent_category.slug, product_category.slug, 'kategori-kosong')";
    }

    protected function rootCategoryNameExpression(): string
    {
        return "COALESCE(great_great_grandparent_category.name, great_grandparent_category.name, grandparent_category.name, parent_category.name, product_category.name, 'Kategori Kosong')";
    }

    protected function categoryPathExpression(): string
    {
        return "CONCAT_WS(' / ', great_great_grandparent_category.name, great_grandparent_category.name, grandparent_category.name, parent_category.name, product_category.name)";
    }

    protected function productRootCategoryOrderSql(string $column): string
    {
        $slugs = collect(['aplikasi-premium'])
            ->merge(WiboostCatalog::coreCategorySlugs())
            ->unique()
            ->values();

        return 'CASE ' . $column . ' ' . $slugs
            ->map(fn (string $slug, int $index) => "WHEN '{$slug}' THEN {$index}")
            ->implode(' ') . ' ELSE ' . $slugs->count() . ' END';
    }

    protected function hideRemovedCatalogProducts($query): void
    {
        $removedSlugs = WiboostCatalog::removedCategorySlugs();

        $query->where(function ($query) use ($removedSlugs) {
            $query->whereNull('category_id')
                ->orWhereHas('category', function ($categoryQuery) use ($removedSlugs) {
                    $this->hideRemovedCatalogCategories($categoryQuery, $removedSlugs);
                });
        });
    }

    protected function hideRemovedCatalogCategories($query, ?array $removedSlugs = null): void
    {
        $removedSlugs ??= WiboostCatalog::removedCategorySlugs();

        $query->whereNotIn('slug', $removedSlugs)
            ->where(function ($query) {
                $query->whereNull('fulfillment_type')
                    ->orWhere('fulfillment_type', '!=', 'manual_action');
            })
            ->whereDoesntHave('parent', function ($parentQuery) use ($removedSlugs) {
                $parentQuery->whereIn('slug', $removedSlugs)
                    ->orWhere('fulfillment_type', 'manual_action')
                    ->orWhereHas('parent', function ($grandParentQuery) use ($removedSlugs) {
                        $grandParentQuery->whereIn('slug', $removedSlugs)
                            ->orWhere('fulfillment_type', 'manual_action')
                            ->orWhereHas('parent', function ($greatGrandParentQuery) use ($removedSlugs) {
                                $greatGrandParentQuery->whereIn('slug', $removedSlugs)
                                    ->orWhere('fulfillment_type', 'manual_action');
                            });
                    });
            });
    }
}
