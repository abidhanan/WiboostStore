<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('parent.parent.parent')
            ->withCount(['children', 'products'])
            ->orderBy('name')
            ->orderBy('parent_id');

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

        $categories = $query->get();

        $categorySections = [
            'main' => $categories->filter(fn (Category $category) => $category->depth === 0)->values(),
            'level_1' => $categories->filter(fn (Category $category) => $category->depth === 1)->values(),
            'level_2' => $categories->filter(fn (Category $category) => $category->depth === 2)->values(),
            'level_3' => $categories->filter(fn (Category $category) => $category->depth >= 3)->values(),
        ];

        return view('admin.categories.index', [
            'categories' => $categories,
            'categorySections' => $categorySections,
            'search' => $request->search,
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
            'fulfillment_type' => 'nullable|in:auto_api,stock_based,manual_action',
        ]);

        $parent = $request->filled('parent_id')
            ? Category::findOrFail($request->parent_id)
            : null;
        $fulfillmentType = $parent?->fulfillment_type ?? $request->fulfillment_type;
        $slug = $this->resolveSlug($request);

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
                return redirect()
                    ->route('admin.categories.index')
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
            $imagePath = $request->file('image')->store('categories', 'public');
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

        return redirect()->route('admin.categories.index')->with('success', 'Kategori baru berhasil ditambahkan!');
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
            'fulfillment_type' => 'nullable|in:auto_api,stock_based,manual_action',
        ]);

        $parent = $request->filled('parent_id')
            ? Category::findOrFail($request->parent_id)
            : null;
        $fulfillmentType = $parent?->fulfillment_type ?? $request->fulfillment_type;
        $slug = $this->resolveSlug($request);

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

            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui!');
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
}
