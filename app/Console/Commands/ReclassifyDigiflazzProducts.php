<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Support\WiboostCatalog;
use Illuminate\Console\Command;

class ReclassifyDigiflazzProducts extends Command
{
    protected $signature = 'wiboost:reclassify-digiflazz';

    protected $description = 'Perbaiki kategori produk Digiflazz yang sudah terimpor berdasarkan nama dan SKU';

    public function handle(): int
    {
        $fallbackId = Category::query()->orderBy('id')->value('id');

        if (! $fallbackId) {
            $this->error('Belum ada kategori di database.');

            return self::FAILURE;
        }

        $products = Product::where('provider_source', 'digiflazz')->get();

        if ($products->isEmpty()) {
            $this->info('Tidak ada produk Digiflazz yang perlu diperbaiki.');

            return self::SUCCESS;
        }

        $changed = 0;
        $removed = 0;
        $deactivated = 0;

        foreach ($products as $product) {
            $targetTopCategorySlug = WiboostCatalog::resolveDigiflazzTopCategorySlug(
                $product->name ?? '',
                '',
                '',
                trim(($product->provider_product_id ?? '') . ' ' . ($product->description ?? ''))
            );

            if ($targetTopCategorySlug === null) {
                if (! $product->transactions()->exists()) {
                    $product->delete();
                    $removed++;
                    continue;
                }

                if ($product->is_active) {
                    $product->update([
                        'is_active' => false,
                        'status' => 'inactive',
                    ]);
                    $deactivated++;
                }

                continue;
            }

            $targetSubcategorySlug = WiboostCatalog::resolveDigiflazzSubcategorySlug(
                $targetTopCategorySlug,
                $product->name ?? '',
                $product->provider_product_id ?? '',
                $product->description ?? ''
            );
            $targetCategoryId = $targetSubcategorySlug
                ? (Category::query()->where('slug', $targetSubcategorySlug)->value('id')
                    ?? $this->categoryIdBySlug($targetTopCategorySlug, $fallbackId))
                : $this->categoryIdBySlug($targetTopCategorySlug, $fallbackId);
            $targetMeta = WiboostCatalog::targetMetaForTopCategory($targetTopCategorySlug) ?? [
                'label' => null,
                'placeholder' => null,
                'hint' => null,
            ];

            if (
                (int) $product->category_id === $targetCategoryId
                && $product->target_label === $targetMeta['label']
                && $product->target_placeholder === $targetMeta['placeholder']
                && $product->target_hint === $targetMeta['hint']
            ) {
                continue;
            }

            $product->update([
                'category_id' => $targetCategoryId,
                'target_label' => $targetMeta['label'],
                'target_placeholder' => $targetMeta['placeholder'],
                'target_hint' => $targetMeta['hint'],
            ]);

            $changed++;
        }

        $this->info("Reclassify selesai. {$changed} produk diperbarui, {$removed} produk dihapus, {$deactivated} produk dinonaktifkan.");

        return self::SUCCESS;
    }

    protected function categoryIdBySlug(string $slug, int $fallbackId): int
    {
        return Category::query()->where('slug', $slug)->value('id') ?? $fallbackId;
    }
}
