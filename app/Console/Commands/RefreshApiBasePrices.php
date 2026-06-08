<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\DigiflazzService;
use App\Services\OrderSosmedService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RefreshApiBasePrices extends Command
{
    protected $signature = 'wiboost:refresh-api-base-prices
        {--provider=all : all, ordersosmed, atau digiflazz}
        {--execute : Simpan perubahan harga dasar ke database}
        {--limit= : Batasi jumlah produk lokal yang dicek per provider}
        {--keep-current-if-higher : Jangan turunkan harga tampil lokal jika harga provider terbaru lebih rendah}';

    protected $description = 'Refresh harga dasar produk API dari provider tanpa mengubah kategori, mapping, atau daftar produk.';

    public function handle(OrderSosmedService $orderSosmedService, DigiflazzService $digiflazzService): int
    {
        $provider = Str::lower((string) $this->option('provider'));
        $execute = (bool) $this->option('execute');
        $limit = filled($this->option('limit')) ? max(1, (int) $this->option('limit')) : null;
        $keepCurrentIfHigher = (bool) $this->option('keep-current-if-higher');

        if (! in_array($provider, ['all', 'ordersosmed', 'digiflazz'], true)) {
            $this->error('Provider harus salah satu dari: all, ordersosmed, digiflazz.');

            return self::FAILURE;
        }

        $this->info($execute
            ? 'Mode execute aktif: harga dasar API yang berbeda akan diperbarui.'
            : 'Mode preview: belum ada data yang diubah. Tambahkan --execute untuk menyimpan.');

        $hasFailure = false;

        if (in_array($provider, ['all', 'ordersosmed'], true)) {
            $hasFailure = ! $this->refreshOrderSosmed($orderSosmedService, $execute, $limit, $keepCurrentIfHigher) || $hasFailure;
        }

        if (in_array($provider, ['all', 'digiflazz'], true)) {
            $hasFailure = ! $this->refreshDigiflazz($digiflazzService, $execute, $limit, $keepCurrentIfHigher) || $hasFailure;
        }

        return $hasFailure ? self::FAILURE : self::SUCCESS;
    }

    protected function refreshOrderSosmed(OrderSosmedService $service, bool $execute, ?int $limit, bool $keepCurrentIfHigher): bool
    {
        $this->newLine();
        $this->info('Mengambil harga dasar terbaru dari OrderSosmed...');

        $response = $service->getServices();

        if (! ($response['success'] ?? false) || empty($response['data']) || ! is_array($response['data'])) {
            $this->error('Gagal mengambil layanan OrderSosmed: ' . ($response['message'] ?? 'Provider tidak mengembalikan data.'));

            return false;
        }

        $providerServices = $this->mapOrderSosmedServices($response['data']);
        $result = $this->refreshProductsFromMap('ordersosmed', $providerServices, $execute, $limit, $keepCurrentIfHigher);

        $this->renderResult('OrderSosmed', $result);

        return true;
    }

    protected function refreshDigiflazz(DigiflazzService $service, bool $execute, ?int $limit, bool $keepCurrentIfHigher): bool
    {
        $this->newLine();
        $this->info('Mengambil harga dasar terbaru dari Digiflazz...');

        $response = $service->getPriceList();
        $items = $response['raw']['data'] ?? [];

        if (! ($response['success'] ?? false) || ! is_array($items) || $items === []) {
            $this->error('Gagal mengambil price list Digiflazz: ' . ($response['message'] ?? 'Provider tidak mengembalikan data.'));

            return false;
        }

        $providerProducts = [];

        foreach ($items as $item) {
            if (! is_array($item) || blank($item['buyer_sku_code'] ?? null)) {
                continue;
            }

            $price = $this->parseProviderNumber($item['price'] ?? 0);

            if ($price <= 0) {
                continue;
            }

            $providerProducts[(string) $item['buyer_sku_code']] = [
                'base_price' => $price,
                'provider_quantity' => 1,
            ];
        }

        $result = $this->refreshProductsFromMap('digiflazz', $providerProducts, $execute, $limit, $keepCurrentIfHigher);

        $this->renderResult('Digiflazz', $result);

        return true;
    }

    protected function refreshProductsFromMap(string $provider, array $providerProducts, bool $execute, ?int $limit, bool $keepCurrentIfHigher): array
    {
        $query = Product::query()
            ->where(function ($query) use ($provider) {
                $query->where('provider_source', $provider)
                    ->orWhere('provider_id', $provider);
            })
            ->with('category.parent.parent.parent')
            ->orderBy('id');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $checked = 0;
        $changed = 0;
        $same = 0;
        $skipped = 0;
        $displayIncreased = 0;
        $displayDecreased = 0;
        $displayKeptHigher = 0;
        $examples = [];

        foreach ($query->get() as $product) {
            $checked++;

            $providerProduct = $this->resolveProviderProductForLocalProduct($product, $providerProducts);

            if ($providerProduct === null) {
                $skipped++;
                continue;
            }

            $newPrice = (float) $providerProduct['base_price'];
            $newQuantity = max(1, (int) ($providerProduct['provider_quantity'] ?? 1));
            $oldPrice = (float) $product->price;
            $oldQuantity = max(1, (int) ($product->provider_quantity ?: 1));
            $oldDisplayPrice = $this->resolveDisplayedSellingPrice($product, $provider, $oldPrice, $oldQuantity);
            $providerDisplayPrice = $this->resolveDisplayedSellingPrice($product, $provider, $newPrice, $newQuantity);

            if ($keepCurrentIfHigher && $oldDisplayPrice > $providerDisplayPrice) {
                $newPrice = $this->resolveBasePriceForDisplayedPrice($product, $provider, $oldDisplayPrice, $newQuantity);
                $displayKeptHigher++;
            }

            $newDisplayPrice = $this->resolveDisplayedSellingPrice($product, $provider, $newPrice, $newQuantity);
            $isDifferent = abs($oldPrice - $newPrice) > 0.01 || $oldQuantity !== $newQuantity;

            if (! $isDifferent) {
                $same++;
                continue;
            }

            $changed++;

            if ($newDisplayPrice > $oldDisplayPrice) {
                $displayIncreased++;
            } elseif ($newDisplayPrice < $oldDisplayPrice) {
                $displayDecreased++;
            }

            if (count($examples) < 8) {
                $examples[] = [
                    $product->id,
                    Str::limit($product->name, 42),
                    $this->formatCurrency($oldPrice),
                    $this->formatCurrency($newPrice),
                    $this->formatCurrency($oldDisplayPrice),
                    $this->formatCurrency($newDisplayPrice),
                    $this->resolveChangeLabel($oldDisplayPrice, $newDisplayPrice),
                    $oldQuantity,
                    $newQuantity,
                ];
            }

            if ($execute) {
                $product->forceFill([
                    'price' => $newPrice,
                    'provider_quantity' => $newQuantity,
                    'provider_source' => $provider,
                    'provider_id' => $product->provider_id ?: $provider,
                ])->saveQuietly();
            }
        }

        return compact('checked', 'changed', 'same', 'skipped', 'displayIncreased', 'displayDecreased', 'displayKeptHigher', 'examples');
    }

    protected function resolveDisplayedSellingPrice(Product $product, string $provider, float $basePrice, int $providerQuantity): float
    {
        $currentPrice = $product->price;
        $currentQuantity = $product->provider_quantity;

        $product->setAttribute('price', $basePrice);
        $product->setAttribute('provider_quantity', $providerQuantity);

        $displayPrice = $provider === 'ordersosmed'
            ? (float) $product->original_ordersosmed_price_per_thousand
            : (float) $product->original_selling_price;

        $product->setAttribute('price', $currentPrice);
        $product->setAttribute('provider_quantity', $currentQuantity);

        return $displayPrice;
    }

    protected function resolveBasePriceForDisplayedPrice(Product $product, string $provider, float $displayPrice, int $providerQuantity): float
    {
        $markupRate = (float) $product->api_selling_markup_rate;
        $divider = 1 + $markupRate;

        if ($divider <= 0) {
            return $displayPrice;
        }

        if ($provider === 'ordersosmed') {
            return max(0, ($displayPrice / 1000) * max(1, $providerQuantity) / $divider);
        }

        return max(0, $displayPrice / $divider);
    }

    protected function resolveChangeLabel(float $oldDisplayPrice, float $newDisplayPrice): string
    {
        if ($newDisplayPrice > $oldDisplayPrice) {
            return 'Naik';
        }

        if ($newDisplayPrice < $oldDisplayPrice) {
            return 'Turun';
        }

        return 'Tetap';
    }

    protected function resolveProviderProductForLocalProduct(Product $product, array $providerProducts): ?array
    {
        $providerProductId = (string) $product->provider_product_id;

        if (isset($providerProducts[$providerProductId])) {
            return $providerProducts[$providerProductId];
        }

        $plainId = str_contains($providerProductId, ':')
            ? Str::after($providerProductId, ':')
            : $providerProductId;

        return $providerProducts[$plainId] ?? null;
    }

    protected function mapOrderSosmedServices(array $services): array
    {
        $mapped = [];

        foreach ($services as $service) {
            if (! is_array($service)) {
                continue;
            }

            $providerProductId = $this->resolveOrderSosmedProviderProductId($service);
            $plainId = (string) ($service['id'] ?? $service['service'] ?? $service['service_id'] ?? '');

            if ($providerProductId === null || $plainId === '') {
                continue;
            }

            $providerQuantity = $this->resolveOrderSosmedProviderQuantity($service);
            $basePrice = $this->resolveOrderSosmedBasePrice($service, $providerQuantity);

            if ($basePrice <= 0) {
                continue;
            }

            $payload = [
                'base_price' => $basePrice,
                'provider_quantity' => $providerQuantity,
            ];

            $mapped[$providerProductId] = $payload;
            $mapped[$plainId] ??= $payload;
        }

        return $mapped;
    }

    protected function resolveOrderSosmedProviderProductId(array $service): ?string
    {
        $id = $service['id'] ?? $service['service'] ?? $service['service_id'] ?? null;

        if (! filled($id)) {
            return null;
        }

        $catalogType = $service['_ordersosmed_catalog_type'] ?? null;

        return filled($catalogType) ? $catalogType . ':' . $id : (string) $id;
    }

    protected function resolveOrderSosmedProviderQuantity(array $service): int
    {
        if ($this->usesOrderSosmedPerThousandPricing($service)) {
            return 1000;
        }

        $min = (int) round($this->parseProviderNumber($service['min'] ?? $service['minimum'] ?? 0));

        return max(1, $min);
    }

    protected function resolveOrderSosmedBasePrice(array $service, int $providerQuantity): float
    {
        if (($service['_ordersosmed_pricing_mode'] ?? null) === 'flat') {
            $price = $this->parseProviderNumber($service['price'] ?? $service['rate'] ?? 0);

            return max(0, $price);
        }

        if (isset($service['price']) && $this->parseProviderNumber($service['price']) > 0) {
            return max(0, $this->parseProviderNumber($service['price']));
        }

        if (isset($service['rate']) && $this->parseProviderNumber($service['rate']) > 0) {
            $rate = $this->parseProviderNumber($service['rate'] ?? 0);

            return max(0, $rate);
        }

        return 0;
    }

    protected function usesOrderSosmedPerThousandPricing(array $service): bool
    {
        return ($service['_ordersosmed_pricing_mode'] ?? null) !== 'flat';
    }

    protected function parseProviderNumber(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $text = trim((string) $value);

        if ($text === '') {
            return 0.0;
        }

        $text = preg_replace('/[^\d,.\-]+/', '', str_replace(["\xc2\xa0", ' '], '', $text)) ?: '';

        if ($text === '' || $text === '-') {
            return 0.0;
        }

        $negative = str_starts_with($text, '-');
        $text = ltrim($text, '-');

        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $text) === 1) {
            $text = str_replace('.', '', $text);
            $text = str_replace(',', '.', $text);
        } elseif (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $text) === 1) {
            $text = str_replace(',', '', $text);
        } elseif (str_contains($text, ',') && ! str_contains($text, '.')) {
            $text = str_replace(',', '.', $text);
        } elseif (substr_count($text, '.') > 1) {
            $text = str_replace('.', '', $text);
        } elseif (substr_count($text, ',') > 1) {
            $text = str_replace(',', '', $text);
        }

        return (float) (($negative ? '-' : '') . $text);
    }

    protected function renderResult(string $label, array $result): void
    {
        $this->table(
            ['Provider', 'Dicek', 'Berbeda', 'Sudah sama', 'Tidak ketemu'],
            [[
                $label,
                $result['checked'],
                $result['changed'],
                $result['same'],
                $result['skipped'],
            ]]
        );

        if (($result['examples'] ?? []) !== []) {
            $this->table(
                ['ID', 'Produk', 'Modal lama', 'Modal provider baru', 'Harga tampil lama', 'Harga tampil baru + markup', 'Arah', 'Qty lama', 'Qty baru'],
                $result['examples']
            );
        }

        $this->line(sprintf(
            'Ringkasan harga tampil: naik %d, turun %d, ditahan tetap lebih tinggi %d.',
            $result['displayIncreased'] ?? 0,
            $result['displayDecreased'] ?? 0,
            $result['displayKeptHigher'] ?? 0,
        ));
    }

    protected function formatCurrency(float $value): string
    {
        return 'Rp ' . number_format($value, 2, ',', '.');
    }
}
