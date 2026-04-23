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

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category.parent.parent')->latest();

        if ($request->filled('search')) {
            $query->where(function ($innerQuery) use ($request) {
                $innerQuery->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('provider_product_id', 'like', '%' . $request->search . '%');
            });
        }

        $products = $query->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::with('parent.parent')
            ->where(function ($query) {
                $query->whereNotNull('parent_id')
                    ->orDoesntHave('children');
            })
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);
        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create($this->buildPayload($validated, $request, $imagePath));

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::with('parent.parent')
            ->where(function ($query) {
                $query->whereNotNull('parent_id')
                    ->orDoesntHave('children');
            })
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get();

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

        return redirect()->route('admin.products.index')->with('success', 'Perubahan produk berhasil disimpan.');
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
        return $this->runSyncCommand('sync:ordersosmed', 'OrderSosmed');
    }

    protected function validateProduct(Request $request, ?int $productId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
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
            'process_type' => 'required|in:api,account,number,manual',
            'target_label' => 'nullable|string|max:255',
            'target_placeholder' => 'nullable|string|max:255',
            'target_hint' => 'nullable|string|max:1000',
            'requires_buyer_email' => 'nullable|boolean',
            'stock_reminder' => 'nullable|integer|min:0',
            'is_active' => 'required|in:0,1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'emote' => 'nullable|string|max:50',
        ]);
    }

    protected function buildPayload(array $validated, Request $request, ?string $imagePath, ?Product $product = null): array
    {
        $requiresTargetInput = in_array($validated['process_type'], ['api', 'manual'], true);
        $usesInventory = in_array($validated['process_type'], ['account', 'number'], true);
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
            'is_active' => $validated['is_active'],
            'status' => ((int) $validated['is_active']) === 1 ? 'active' : 'inactive',
        ];
    }

    protected function runSyncCommand(string $command, string $providerLabel)
    {
        try {
            $exitCode = Artisan::call($command);
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
}
