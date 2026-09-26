<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Support\WiboostCatalog;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categoryFilter = (string) $request->query('category_filter', 'all');
        $allowedCategoryFilters = ['all', 'main', 'level_1', 'level_2', 'level_3'];

        if (! in_array($categoryFilter, $allowedCategoryFilters, true)) {
            $categoryFilter = 'all';
        }

        $query = Category::with('parent.parent.parent')
            ->withCount(['children', 'products'])
            ->orderBy('name')
            ->orderBy('parent_id');

        $this->hideRemovedCatalogCategories($query);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('parent', function ($parentQuery) use ($search) {
                        $parentQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('slug', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('parent.parent', function ($parentQuery) use ($search) {
                        $parentQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('slug', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('parent.parent.parent', function ($parentQuery) use ($search) {
                        $parentQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('slug', 'like', '%' . $search . '%');
                    });
            });
        }

        $filteredCategories = $query->get()
            ->when($categoryFilter === 'main', fn ($items) => $items->filter(fn (Category $category) => $category->depth === 0))
            ->when($categoryFilter === 'level_1', fn ($items) => $items->filter(fn (Category $category) => $category->depth === 1))
            ->when($categoryFilter === 'level_2', fn ($items) => $items->filter(fn (Category $category) => $category->depth === 2))
            ->when($categoryFilter === 'level_3', fn ($items) => $items->filter(fn (Category $category) => $category->depth >= 3))
            ->values();

        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(200, max(50, (int) $request->query('per_page', 120)));
        $categories = new LengthAwarePaginator(
            $filteredCategories->forPage($page, $perPage)->values(),
            $filteredCategories->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $categoryItems = $categories->getCollection();

        $categorySections = [
            'main' => $categoryItems->filter(fn (Category $category) => $category->depth === 0)->values(),
            'level_1' => $categoryItems->filter(fn (Category $category) => $category->depth === 1)->values(),
            'level_2' => $categoryItems->filter(fn (Category $category) => $category->depth === 2)->values(),
            'level_3' => $categoryItems->filter(fn (Category $category) => $category->depth >= 3)->values(),
        ];

        return view('admin.categories.index', [
            'categories' => $categories,
            'categorySections' => $categorySections,
            'search' => $request->search,
            'categoryFilter' => $categoryFilter,
        ]);
    }

    public function create()
    {
        $parentOptions = $this->buildParentOptions();

        return view('admin.categories.create', compact('parentOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'emote' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'fulfillment_type' => 'nullable|in:auto_api,stock_based',
        ]);

        $parent = $request->filled('parent_id')
            ? Category::findOrFail($request->parent_id)
            : null;
        $fulfillmentType = $parent?->fulfillment_type ?? $request->fulfillment_type;
        $slug = $this->resolveSlug($request);

        if (in_array($slug, WiboostCatalog::removedCategorySlugs(), true)) {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' => 'Kategori Nomor Luar dan Buzzer sudah dihapus dari katalog aktif admin.',
                ]);
        }

        if (! $fulfillmentType) {
            return back()
                ->withInput()
                ->withErrors([
                    'fulfillment_type' => 'Pilih tipe fulfillment untuk kategori utama.',
                ]);
        }

        if ($slug === '') {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' => 'Slug sudah digunakan atau tidak valid. Gunakan nama/slug yang berbeda.',
                ]);
        }

        $existingCategory = Category::query()->where('slug', $slug)->first();

        if ($existingCategory) {
            if ($this->matchesSubmittedCategory($existingCategory, $request)) {
                return $this->redirectToReturnTo($request, 'admin.categories.index')
                    ->with('success', 'Kategori baru berhasil ditambahkan!');
            }

            return back()
                ->withInput()
                ->withErrors([
                    'slug' => 'Slug sudah digunakan atau tidak valid. Gunakan nama/slug yang berbeda.',
                ]);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = app(\App\Support\WebpImageOptimizer::class)
                ->storeUploadedFile($request->file('image'), 'categories');
        }

        Category::create([
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $parent ? $request->description : null,
            'emote' => $request->emote,
            'image' => $imagePath,
            'fulfillment_type' => $fulfillmentType,
        ]);

        return $this->redirectToReturnTo($request, 'admin.categories.index')->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    public function edit(Category $category)
    {
        $excludedIds = array_merge([$category->id], $this->collectDescendantIds($category));
        $parentOptions = $this->buildParentOptions($excludedIds);

        return view('admin.categories.edit', compact('category', 'parentOptions'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'emote' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'fulfillment_type' => 'nullable|in:auto_api,stock_based',
        ]);

        $parent = $request->filled('parent_id')
            ? Category::findOrFail($request->parent_id)
            : null;
        $fulfillmentType = $parent?->fulfillment_type ?? $request->fulfillment_type;
        $slug = $this->resolveSlug($request);

        if (in_array($slug, WiboostCatalog::removedCategorySlugs(), true)) {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' => 'Kategori Nomor Luar dan Buzzer sudah dihapus dari katalog aktif admin.',
                ]);
        }

        if (! $fulfillmentType) {
            return back()
                ->withInput()
                ->withErrors([
                    'fulfillment_type' => 'Pilih tipe fulfillment untuk kategori utama.',
                ]);
        }

        if ($slug === '' || Category::query()->where('slug', $slug)->whereKeyNot($category->id)->exists()) {
            return back()
                ->withInput()
                ->withErrors([
                    'slug' => 'Slug sudah digunakan atau tidak valid. Gunakan nama/slug yang berbeda.',
                ]);
        }

        $data = [
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $parent ? $request->description : null,
            'emote' => $request->emote,
            'fulfillment_type' => $fulfillmentType,
        ];

        if ($request->boolean('remove_image') && $category->image) {
            Storage::disk('public')->delete($category->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $data['image'] = app(\App\Support\WebpImageOptimizer::class)
                ->storeUploadedFile($request->file('image'), 'categories');
        }

        $category->update($data);

        return $this->redirectToReturnTo($request, 'admin.categories.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0 || $category->children()->count() > 0) {
            return back()->with('error', 'Gagal hapus! Kategori ini masih memiliki sub-kategori atau produk aktif.');
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus dari sistem!');
    }

    protected function buildParentOptions(array $excludedIds = []): array
    {
        $categories = Category::with('childrenRecursive')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return $this->flattenCategoryOptions($categories, 0, $excludedIds);
    }

    protected function flattenCategoryOptions($categories, int $depth = 0, array $excludedIds = []): array
    {
        $options = [];

        foreach ($categories as $category) {
            if (in_array($category->id, $excludedIds, true)) {
                continue;
            }

            if ($this->isRemovedCatalogCategory($category)) {
                continue;
            }

            $options[] = [
                'id' => $category->id,
                'label' => str_repeat('— ', $depth) . $category->name,
            ];

            if ($category->childrenRecursive->isNotEmpty()) {
                $options = array_merge(
                    $options,
                    $this->flattenCategoryOptions($category->childrenRecursive, $depth + 1, $excludedIds)
                );
            }
        }

        return $options;
    }

    protected function collectDescendantIds(Category $category): array
    {
        $category->loadMissing('childrenRecursive');

        $ids = [];

        foreach ($category->childrenRecursive as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->collectDescendantIds($child));
        }

        return array_values(array_unique($ids));
    }

    protected function resolveSlug(Request $request): string
    {
        return Str::slug($request->filled('slug') ? $request->slug : $request->name);
    }

    protected function matchesSubmittedCategory(Category $category, Request $request): bool
    {
        $submittedParentId = $request->filled('parent_id') ? (int) $request->parent_id : null;
        $existingParentId = $category->parent_id ? (int) $category->parent_id : null;

        return Str::lower(trim((string) $category->name)) === Str::lower(trim((string) $request->name))
            && $existingParentId === $submittedParentId;
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

    protected function isRemovedCatalogCategory(Category $category): bool
    {
        $removedSlugs = WiboostCatalog::removedCategorySlugs();

        if (in_array($category->slug, $removedSlugs, true) || $category->fulfillment_type === 'manual_action') {
            return true;
        }

        $parent = $category->parent;

        while ($parent) {
            if (in_array($parent->slug, $removedSlugs, true) || $parent->fulfillment_type === 'manual_action') {
                return true;
            }

            $parent = $parent->parent;
        }

        return false;
    }
}
