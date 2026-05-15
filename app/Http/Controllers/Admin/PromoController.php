<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PromoController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $promos = Promo::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('badge_text', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('theme', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        return view('admin.promos.index', compact('promos'));
    }

    public function create()
    {
        $submissionToken = (string) Str::uuid();
        $tokens = session('admin_promo_create_tokens', []);
        $tokens[$submissionToken] = true;
        session(['admin_promo_create_tokens' => $tokens]);

        return view('admin.promos.create', compact('submissionToken'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(includeToken: true));

        if (! $this->consumeCreateToken($validated['submission_token'])) {
            return redirect()
                ->route('admin.promos.index')
                ->with('success', 'Banner sudah diproses sebelumnya. Data tidak dibuat dobel.');
        }

        $data = collect($validated)->except('submission_token')->all();
        $data = $this->normalizePayload($data, $request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('promos', 'public');
        }

        Promo::create($data);

        return redirect()->route('admin.promos.index')->with('success', 'Banner Promo berhasil ditambahkan.');
    }

    public function edit(Promo $promo)
    {
        return view('admin.promos.edit', compact('promo'));
    }

    public function update(Request $request, Promo $promo)
    {
        $validated = $request->validate($this->rules());
        $data = $this->normalizePayload($validated, $request);

        if ($request->boolean('remove_image') && $promo->image) {
            Storage::disk('public')->delete($promo->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($promo->image) {
                Storage::disk('public')->delete($promo->image);
            }

            $data['image'] = $request->file('image')->store('promos', 'public');
        }

        $promo->update($data);

        return redirect()->route('admin.promos.index')->with('success', 'Banner Promo berhasil diperbarui.');
    }

    public function destroy(Promo $promo)
    {
        if ($promo->image) {
            Storage::disk('public')->delete($promo->image);
        }

        $promo->delete();

        return back()->with('success', 'Banner Promo berhasil dihapus.');
    }

    protected function rules(bool $includeToken = false): array
    {
        $rules = [
            'badge_text' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'emoji' => 'nullable|string|max:10',
            'theme' => 'nullable|in:blue,teal,orange,rose',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'link' => 'nullable|url|max:500',
            'is_active' => 'nullable|boolean',
        ];

        if ($includeToken) {
            $rules['submission_token'] = 'required|string';
        }

        return $rules;
    }

    protected function normalizePayload(array $data, Request $request): array
    {
        return [
            'badge_text' => trim((string) ($data['badge_text'] ?? '')),
            'title' => trim((string) ($data['title'] ?? '')),
            'description' => trim((string) ($data['description'] ?? '')),
            'emoji' => trim((string) ($data['emoji'] ?? '')),
            'theme' => ($data['theme'] ?? '') ?: 'blue',
            'link' => $data['link'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    protected function consumeCreateToken(string $token): bool
    {
        $tokens = session('admin_promo_create_tokens', []);

        if (! isset($tokens[$token])) {
            return false;
        }

        unset($tokens[$token]);
        session(['admin_promo_create_tokens' => $tokens]);

        return true;
    }
}
