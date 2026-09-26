<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Display;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DisplayController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $displays = Display::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('badge_text', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('theme', 'like', "%{$search}%");
                });
            })
            ->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.displays.index', compact('displays'));
    }

    public function create()
    {
        $submissionToken = (string) Str::uuid();
        $tokens = session('admin_display_create_tokens', []);
        $tokens[$submissionToken] = true;
        session(['admin_display_create_tokens' => $tokens]);

        return view('admin.displays.create', compact('submissionToken'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(includeToken: true));

        if (! $this->consumeCreateToken($validated['submission_token'])) {
            return redirect()
                ->route('admin.displays.index')
                ->with('success', 'Display sudah diproses sebelumnya. Data tidak dibuat dobel.');
        }

        $data = $this->normalizePayload(collect($validated)->except('submission_token')->all(), $request);

        if ($request->hasFile('image')) {
            $data['image'] = app(\App\Support\WebpImageOptimizer::class)
                ->storeUploadedFile($request->file('image'), 'displays');
        }

        Display::create($data);

        return $this->redirectToReturnTo($request, 'admin.displays.index')->with('success', 'Display berhasil ditambahkan.');
    }

    public function edit(Display $display)
    {
        return view('admin.displays.edit', compact('display'));
    }

    public function update(Request $request, Display $display)
    {
        $validated = $request->validate($this->rules());
        $data = $this->normalizePayload($validated, $request);

        if ($request->boolean('remove_image') && $display->image) {
            Storage::disk('public')->delete($display->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($display->image) {
                Storage::disk('public')->delete($display->image);
            }

            $data['image'] = app(\App\Support\WebpImageOptimizer::class)
                ->storeUploadedFile($request->file('image'), 'displays');
        }

        $display->update($data);

        return $this->redirectToReturnTo($request, 'admin.displays.index')->with('success', 'Display berhasil diperbarui.');
    }

    public function destroy(Display $display)
    {
        if ($display->image) {
            Storage::disk('public')->delete($display->image);
        }

        $display->delete();

        return back()->with('success', 'Display berhasil dihapus.');
    }

    protected function rules(bool $includeToken = false): array
    {
        $rules = [
            'badge_text' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'emoji' => 'nullable|string|max:20',
            'theme' => 'nullable|in:blue,teal,orange,rose',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
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
            'badge_text' => trim((string) ($data['badge_text'] ?? '')) ?: null,
            'title' => trim((string) ($data['title'] ?? '')) ?: null,
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'emoji' => trim((string) ($data['emoji'] ?? '')) ?: null,
            'theme' => ($data['theme'] ?? '') ?: 'blue',
            'link' => filled($data['link'] ?? null) ? $data['link'] : null,
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    protected function consumeCreateToken(string $token): bool
    {
        $tokens = session('admin_display_create_tokens', []);

        if (! isset($tokens[$token])) {
            return false;
        }

        unset($tokens[$token]);
        session(['admin_display_create_tokens' => $tokens]);

        return true;
    }
}
