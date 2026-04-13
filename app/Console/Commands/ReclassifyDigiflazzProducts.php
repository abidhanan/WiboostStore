<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
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

        foreach ($products as $product) {
            $targetCategoryId = $this->resolveCategoryId($product, $fallbackId);

            if ((int) $product->category_id === $targetCategoryId) {
                continue;
            }

            $product->update([
                'category_id' => $targetCategoryId,
            ]);

            $changed++;
        }

        $this->info("Reclassify selesai. {$changed} produk diperbarui.");

        return self::SUCCESS;
    }

    protected function resolveCategoryId(Product $product, int $fallbackId): int
    {
        $text = strtolower(trim(($product->name ?? '') . ' ' . ($product->provider_product_id ?? '') . ' ' . ($product->description ?? '')));
        $utilityKeywords = ['pulsa', 'data', 'kuota', 'paket', 'internet', 'e-money', 'dana', 'gopay', 'ovo', 'shopeepay', 'linkaja', 'axis', 'telkomsel', 'by.u', 'tri', 'indosat', 'smartfren', 'xl'];
        $gameKeywords = ['game', 'games', 'diamond', 'uc', 'valorant', 'steam', 'voucher', 'pubg', 'codm', 'point blank', 'genshin'];

        if (str_contains($text, 'mobile legends') || str_contains($text, 'mobilelegend')) {
            return $this->categoryIdBySlug('mobile-legends', $fallbackId);
        }

        if (str_contains($text, 'free fire') || str_contains($text, 'freefire')) {
            return $this->categoryIdBySlug('free-fire', $fallbackId);
        }

        if (str_contains($text, 'netflix')) {
            return $this->categoryIdBySlug('netflix', $fallbackId);
        }

        foreach ($utilityKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return $this->categoryIdBySlug('kuota-murah', $fallbackId);
            }
        }

        foreach ($gameKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return $this->categoryIdBySlug('top-up-game', $fallbackId);
            }
        }

        return $fallbackId;
    }

    protected function categoryIdBySlug(string $slug, int $fallbackId): int
    {
        return Category::query()->where('slug', $slug)->value('id') ?? $fallbackId;
    }
}
