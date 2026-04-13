<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Services\OrderSosmedService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncOrderSosmedServices extends Command
{
    protected $signature = 'sync:ordersosmed';

    protected $description = 'Sync layanan dari OrderSosmed ke database Wiboost';

    public function handle(OrderSosmedService $orderSosmedService): int
    {
        $defaultCategoryId = Category::query()
            ->where('slug', 'suntik-sosmed')
            ->value('id') ?? Category::query()->orderBy('id')->value('id');

        if (! $defaultCategoryId) {
            $this->error('Gagal sync: belum ada kategori untuk menampung layanan OrderSosmed.');

            return self::FAILURE;
        }

        $this->info('Mengambil daftar layanan dari OrderSosmed...');
        $response = $orderSosmedService->getServices();
        $services = collect($response['data'] ?? []);

        if (! ($response['success'] ?? false) || $services->isEmpty()) {
            $this->error('Gagal ambil layanan OrderSosmed: ' . ($response['message'] ?? 'Unknown error'));

            return self::FAILURE;
        }

        $total = $services->count();
        $success = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($services as $service) {
            try {
                $providerProductId = $this->resolveProviderProductId($service);
                $name = trim((string) ($service['name'] ?? $service['service_name'] ?? $service['service'] ?? ''));

                if ($providerProductId === null || $name === '') {
                    $failed++;
                    $bar->advance();
                    continue;
                }

                $providerQuantity = $this->resolveProviderQuantity($service);
                $basePrice = $this->resolveBasePrice($service, $providerQuantity);
                $sellingPrice = $basePrice > 0
                    ? ceil(($basePrice * 1.10) / 100) * 100
                    : 0;

                $categoryId = $this->resolveCategoryId((string) ($service['category'] ?? $service['type'] ?? ''), $defaultCategoryId);

                $product = Product::firstOrNew([
                    'provider_product_id' => $providerProductId,
                ]);

                if (! $product->exists && blank($product->slug)) {
                    $product->slug = Str::slug($name) . '-' . Str::random(5);
                }

                $product->fill([
                    'category_id' => $categoryId,
                    'provider_id' => 'ordersosmed',
                    'provider_source' => 'ordersosmed',
                    'provider_quantity' => $providerQuantity,
                    'process_type' => 'api',
                    'name' => $name,
                    'description' => $this->buildDescription($service),
                    'price' => $sellingPrice,
                    'provider_product_id' => $providerProductId,
                    'target_label' => 'Link atau username target',
                    'target_placeholder' => 'Contoh: https://instagram.com/username',
                    'target_hint' => 'Gunakan username atau link publik yang aktif agar pesanan tidak gagal.',
                    'is_active' => $sellingPrice > 0,
                    'status' => $sellingPrice > 0 ? 'active' : 'inactive',
                    'stock_reminder' => 0,
                ]);

                $product->save();
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Gagal sync service OrderSosmed: ' . $e->getMessage(), [
                    'service' => $service,
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\nLayanan OrderSosmed selesai disinkronkan.");
        $this->info("Berhasil: {$success}");

        if ($failed > 0) {
            $this->warn("Gagal: {$failed}");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function resolveProviderProductId(array $service): ?string
    {
        $id = $service['id'] ?? $service['service'] ?? $service['service_id'] ?? null;

        return filled($id) ? (string) $id : null;
    }

    protected function resolveProviderQuantity(array $service): int
    {
        $min = (int) ($service['min'] ?? $service['minimum'] ?? 0);

        return max(1, $min);
    }

    protected function resolveBasePrice(array $service, int $providerQuantity): float
    {
        if (isset($service['price']) && is_numeric($service['price'])) {
            return (float) $service['price'];
        }

        $rate = (float) ($service['rate'] ?? 0);

        if ($rate <= 0) {
            return 0;
        }

        return max(0, ($rate / 1000) * $providerQuantity);
    }

    protected function resolveCategoryId(string $providerCategory, int $defaultCategoryId): int
    {
        $category = strtolower($providerCategory);

        if (str_contains($category, 'buzzer')) {
            return Category::query()->where('slug', 'buzzer')->value('id') ?? $defaultCategoryId;
        }

        return Category::query()->where('slug', 'suntik-sosmed')->value('id') ?? $defaultCategoryId;
    }

    protected function buildDescription(array $service): string
    {
        $chunks = array_filter([
            $service['description'] ?? null,
            $service['note'] ?? null,
            isset($service['min']) ? 'Minimum order: ' . $service['min'] : null,
            isset($service['max']) ? 'Maximum order: ' . $service['max'] : null,
        ]);

        return $chunks !== [] ? implode("\n", $chunks) : 'Layanan otomatis dari provider OrderSosmed.';
    }
}
