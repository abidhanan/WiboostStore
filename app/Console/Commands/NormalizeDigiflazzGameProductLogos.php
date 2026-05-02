<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Support\DigiflazzProductLogo;
use Illuminate\Console\Command;

class NormalizeDigiflazzGameProductLogos extends Command
{
    protected $signature = 'wiboost:normalize-digiflazz-game-product-logos
        {--execute : Jalankan update. Tanpa opsi ini hanya preview.}';

    protected $description = 'Ubah logo produk Digiflazz Top Up Game menjadi emoji diamond, voucher, atau cek username.';

    public function handle(): int
    {
        $root = Category::query()->where('slug', 'top-up-game')->first();

        if (! $root) {
            $this->error('Kategori root top-up-game tidak ditemukan.');

            return self::FAILURE;
        }

        $products = Product::query()
            ->with('category.parent.parent.parent')
            ->where('provider_source', 'digiflazz')
            ->whereIn('category_id', $this->categoryIdsWithDescendants($root))
            ->orderBy('name')
            ->get();

        if ($products->isEmpty()) {
            $this->warn('Tidak ada produk Digiflazz Top Up Game yang ditemukan.');

            return self::SUCCESS;
        }

        $changes = $products
            ->map(function (Product $product) {
                $targetEmote = DigiflazzProductLogo::emoteForProduct($product);

                return [
                    'product' => $product,
                    'target_emote' => $targetEmote,
                    'needs_update' => $product->emote !== $targetEmote || filled($product->image),
                ];
            });

        $summaryRows = collect([
            [DigiflazzProductLogo::DIAMOND, 'Diamond', $changes->where('target_emote', DigiflazzProductLogo::DIAMOND)->count(), $changes->where('target_emote', DigiflazzProductLogo::DIAMOND)->where('needs_update', true)->count()],
            [DigiflazzProductLogo::VOUCHER, 'Voucher / token / non-diamond', $changes->where('target_emote', DigiflazzProductLogo::VOUCHER)->count(), $changes->where('target_emote', DigiflazzProductLogo::VOUCHER)->where('needs_update', true)->count()],
            [DigiflazzProductLogo::USERNAME_CHECK, 'Cek username', $changes->where('target_emote', DigiflazzProductLogo::USERNAME_CHECK)->count(), $changes->where('target_emote', DigiflazzProductLogo::USERNAME_CHECK)->where('needs_update', true)->count()],
        ]);

        $this->table(['Emoji', 'Jenis layanan', 'Total produk', 'Akan diupdate'], $summaryRows->all());

        $examples = $changes
            ->where('needs_update', true)
            ->take(10)
            ->map(fn (array $change) => $change['target_emote'] . ' ' . $change['product']->name)
            ->implode("\n");

        if ($examples !== '') {
            $this->line('Contoh produk yang akan diupdate:');
            $this->line($examples);
        }

        if (! $this->option('execute')) {
            $this->warn('Preview saja. Tambahkan --execute untuk benar-benar mengubah emote produk.');

            return self::SUCCESS;
        }

        $updated = 0;

        foreach ($changes->where('needs_update', true) as $change) {
            /** @var Product $product */
            $product = $change['product'];
            $product->forceFill([
                'emote' => $change['target_emote'],
                'image' => null,
            ])->save();

            $updated++;
        }

        $this->info("Logo produk Digiflazz Top Up Game berhasil dirapikan. Diupdate: {$updated}.");

        return self::SUCCESS;
    }

    protected function categoryIdsWithDescendants(Category $root): array
    {
        $ids = [$root->id];
        $queue = [$root->id];

        while ($queue !== []) {
            $childIds = Category::query()
                ->whereIn('parent_id', $queue)
                ->pluck('id')
                ->all();
            $childIds = array_values(array_diff($childIds, $ids));

            if ($childIds === []) {
                break;
            }

            $ids = array_values(array_unique(array_merge($ids, $childIds)));
            $queue = $childIds;
        }

        return $ids;
    }
}
