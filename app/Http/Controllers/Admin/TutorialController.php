<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tutorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TutorialController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $tutorials = Tutorial::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('main_category', 'like', "%{$search}%")
                        ->orWhere('sub_category', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $tutorialSections = $tutorials
            ->groupBy(fn (Tutorial $tutorial) => $tutorial->main_category ?: ($tutorial->category ?: 'Umum'))
            ->map(function ($mainTutorials, $mainCategory) {
                return [
                    'name' => $mainCategory,
                    'subcategories' => $mainTutorials
                        ->groupBy(fn (Tutorial $tutorial) => $tutorial->sub_category ?: ($tutorial->category ?: 'Umum'))
                        ->map(function ($subTutorials, $subCategory) {
                            $firstTutorial = $subTutorials->first();

                            return [
                                'name' => $subCategory,
                                'image' => $firstTutorial?->sub_category_image,
                                'tutorials' => $subTutorials->values(),
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        return view('admin.tutorials.index', compact('tutorials', 'tutorialSections'));
    }

    public function create()
    {
        $submissionToken = (string) Str::uuid();
        $tokens = session('admin_tutorial_create_tokens', []);
        $tokens[$submissionToken] = true;
        session(['admin_tutorial_create_tokens' => $tokens]);

        return view('admin.tutorials.create', [
            'submissionToken' => $submissionToken,
            'mainCategories' => $this->mainCategoryOptions(),
            'subCategories' => $this->subCategoryOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'main_category' => 'required|string|max:100',
            'sub_category' => 'required|string|max:100',
            'sub_category_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'description' => 'nullable|string|max:255',
            'youtube_url' => 'nullable|url|max:500',
            'content' => 'nullable|string',
            'is_active' => 'required|boolean',
            'submission_token' => 'required|string',
        ]);

        if (! $this->consumeCreateToken($validated['submission_token'])) {
            return redirect()
                ->route('admin.tutorials.index')
                ->with('success', 'Tutorial sudah diproses sebelumnya. Data tidak dibuat dobel.');
        }

        $data = collect($validated)->except('submission_token')->all();
        $data['category'] = $validated['main_category'];
        $data['icon'] = null;
        $data['image'] = null;

        if ($request->hasFile('sub_category_image')) {
            $data['sub_category_image'] = $request->file('sub_category_image')->store('tutorials/subcategories', 'public');
        } else {
            $data['sub_category_image'] = $this->existingSubCategoryImage(
                $validated['main_category'],
                $validated['sub_category']
            );
        }

        if (! $data['sub_category_image']) {
            return back()
                ->withInput()
                ->withErrors([
                    'sub_category_image' => 'Logo kategori kedua wajib diupload untuk kategori kedua baru.',
                ]);
        }

        Tutorial::create($data);

        return redirect()->route('admin.tutorials.index')->with('success', 'Tutorial berhasil ditambahkan!');
    }

    public function edit(Tutorial $tutorial)
    {
        return view('admin.tutorials.edit', [
            'tutorial' => $tutorial,
            'mainCategories' => $this->mainCategoryOptions(),
            'subCategories' => $this->subCategoryOptions(),
        ]);
    }

    public function update(Request $request, Tutorial $tutorial)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'main_category' => 'required|string|max:100',
            'sub_category' => 'required|string|max:100',
            'sub_category_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'description' => 'nullable|string|max:255',
            'youtube_url' => 'nullable|url|max:500',
            'content' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $data = collect($validated)->except('sub_category_image')->all();
        $data['category'] = $validated['main_category'];
        $data['icon'] = null;

        if ($request->hasFile('sub_category_image')) {
            $this->deleteTutorialFileIfUnused($tutorial->sub_category_image, $tutorial->id);

            $data['sub_category_image'] = $request->file('sub_category_image')->store('tutorials/subcategories', 'public');
        } elseif (
            $this->sameCategory($tutorial, $validated['main_category'], $validated['sub_category'])
            && $tutorial->sub_category_image
        ) {
            $data['sub_category_image'] = $tutorial->sub_category_image;
        } else {
            $data['sub_category_image'] = $this->existingSubCategoryImage(
                $validated['main_category'],
                $validated['sub_category'],
                $tutorial->id
            );
        }

        if (! $data['sub_category_image']) {
            return back()
                ->withInput()
                ->withErrors([
                    'sub_category_image' => 'Logo kategori kedua wajib diupload untuk kategori kedua baru.',
                ]);
        }

        $tutorial->update($data);

        return redirect()->route('admin.tutorials.index')->with('success', 'Tutorial berhasil diperbarui!');
    }

    public function destroy(Tutorial $tutorial)
    {
        $this->deleteTutorialFileIfUnused($tutorial->image, $tutorial->id);
        $this->deleteTutorialFileIfUnused($tutorial->sub_category_image, $tutorial->id);

        $tutorial->delete();

        return back()->with('success', 'Tutorial berhasil dihapus!');
    }

    protected function consumeCreateToken(string $token): bool
    {
        $tokens = session('admin_tutorial_create_tokens', []);

        if (! isset($tokens[$token])) {
            return false;
        }

        unset($tokens[$token]);
        session(['admin_tutorial_create_tokens' => $tokens]);

        return true;
    }

    protected function mainCategoryOptions()
    {
        return Tutorial::query()
            ->get(['main_category', 'category'])
            ->map(fn (Tutorial $tutorial) => $tutorial->main_category ?: ($tutorial->category ?: 'Umum'))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    protected function subCategoryOptions()
    {
        return Tutorial::query()
            ->get(['sub_category', 'category'])
            ->map(fn (Tutorial $tutorial) => $tutorial->sub_category ?: ($tutorial->category ?: 'Umum'))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    protected function existingSubCategoryImage(string $mainCategory, string $subCategory, ?int $ignoreId = null): ?string
    {
        return Tutorial::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('main_category', $mainCategory)
            ->where('sub_category', $subCategory)
            ->whereNotNull('sub_category_image')
            ->value('sub_category_image');
    }

    protected function sameCategory(Tutorial $tutorial, string $mainCategory, string $subCategory): bool
    {
        return ($tutorial->main_category ?: $tutorial->category) === $mainCategory
            && ($tutorial->sub_category ?: $tutorial->category) === $subCategory;
    }

    protected function deleteTutorialFileIfUnused(?string $path, ?int $ignoreId = null): void
    {
        if (! $path) {
            return;
        }

        $isStillUsed = Tutorial::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where(function ($query) use ($path) {
                $query->where('image', $path)
                    ->orWhere('sub_category_image', $path);
            })
            ->exists();

        if (! $isStillUsed) {
            Storage::disk('public')->delete($path);
        }
    }
}
