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
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('admin.tutorials.index', compact('tutorials'));
    }

    public function create()
    {
        $submissionToken = (string) Str::uuid();
        $tokens = session('admin_tutorial_create_tokens', []);
        $tokens[$submissionToken] = true;
        session(['admin_tutorial_create_tokens' => $tokens]);

        return view('admin.tutorials.create', compact('submissionToken'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'icon'        => 'nullable|string|max:10',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'youtube_url' => 'nullable|url|max:500',
            'content'     => 'nullable|string',
            'is_active'   => 'required|boolean',
            'submission_token' => 'required|string',
        ]);

        if (! $this->consumeCreateToken($validated['submission_token'])) {
            return redirect()
                ->route('admin.tutorials.index')
                ->with('success', 'Tutorial sudah diproses sebelumnya. Data tidak dibuat dobel.');
        }

        $data = collect($validated)->except('submission_token')->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('tutorials', 'public');
        }

        Tutorial::create($data);

        return redirect()->route('admin.tutorials.index')->with('success', 'Tutorial berhasil ditambahkan! 📚');
    }

    public function edit(Tutorial $tutorial)
    {
        return view('admin.tutorials.edit', compact('tutorial'));
    }

    public function update(Request $request, Tutorial $tutorial)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'icon'        => 'nullable|string|max:10',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'youtube_url' => 'nullable|url|max:500',
            'content'     => 'nullable|string',
            'is_active'   => 'required|boolean',
        ]);

        $data = $request->except(['image', 'remove_image']);

        if ($request->has('remove_image') && $request->remove_image == 1) {
            if ($tutorial->image) Storage::disk('public')->delete($tutorial->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($tutorial->image) Storage::disk('public')->delete($tutorial->image);
            $data['image'] = $request->file('image')->store('tutorials', 'public');
        }

        $tutorial->update($data);

        return redirect()->route('admin.tutorials.index')->with('success', 'Tutorial berhasil diperbarui! ✨');
    }

    public function destroy(Tutorial $tutorial)
    {
        if ($tutorial->image) {
            Storage::disk('public')->delete($tutorial->image);
        }
        $tutorial->delete();
        return back()->with('success', 'Tutorial berhasil dihapus! 🗑️');
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
}
