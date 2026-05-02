<?php

namespace App\Console\Commands;

use App\Services\OrderSosmedService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ListOrderSosmedCategories extends Command
{
    protected $signature = 'ordersosmed:list-categories
        {--search= : Filter ID atau nama kategori provider OrderSosmed}
        {--limit=200 : Batas jumlah baris yang ditampilkan, pakai 0 untuk semua}
        {--export= : Simpan hasil ke CSV, contoh: storage/app/ordersosmed-categories.csv}';

    protected $description = 'Tampilkan daftar category_id asli dari API OrderSosmed';

    public function handle(OrderSosmedService $orderSosmedService): int
    {
        $this->info('Mengambil daftar layanan dari OrderSosmed...');

        $response = $orderSosmedService->getServices();

        if (! ($response['success'] ?? false) || empty($response['data'])) {
            $this->error('Gagal mengambil daftar layanan OrderSosmed.');
            $this->warn('Pesan provider: ' . ($response['message'] ?? 'Provider tidak mengembalikan daftar layanan.'));

            return self::FAILURE;
        }

        $rows = $this->buildRows(collect($response['data']));
        $search = trim((string) $this->option('search'));

        if ($search !== '') {
            $rows = $rows->filter(function (array $row) use ($search) {
                return Str::contains(Str::lower($row['ID'] . ' ' . $row['Nama Kategori']), Str::lower($search));
            });
        }

        $rows = $rows->values();
        $this->info('Kategori ditemukan: ' . $rows->count());

        if ($exportPath = trim((string) $this->option('export'))) {
            $this->exportCsv($rows, $exportPath);
            $this->info('CSV tersimpan: ' . $exportPath);
        }

        $limit = max(0, (int) $this->option('limit'));
        $displayRows = $limit > 0 ? $rows->take($limit) : $rows;

        $this->table(['ID', 'Nama Kategori', 'Jumlah Layanan'], $displayRows->values()->all());

        if ($limit > 0 && $rows->count() > $limit) {
            $this->warn('Masih ada ' . ($rows->count() - $limit) . ' kategori lain. Pakai --limit=0 untuk menampilkan semua.');
        }

        $this->newLine();
        $this->line('Contoh import ke kategori lokal SOSMED 2:');
        $this->line('/opt/alt/php83/usr/bin/php artisan sync:ordersosmed --provider-category-ids=6735,6276 --target-category="SOSMED 2"');

        return self::SUCCESS;
    }

    protected function buildRows(Collection $services): Collection
    {
        return $services
            ->groupBy(function (array $service) {
                $id = $this->resolveProviderCategoryId($service) ?? '-';
                $name = $this->resolveProviderCategoryName($service) ?: '-';

                return $id . '|' . $name;
            })
            ->map(function (Collection $items, string $key) {
                [$id, $name] = array_pad(explode('|', $key, 2), 2, '-');

                return [
                    'ID' => $id,
                    'Nama Kategori' => html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'Jumlah Layanan' => $items->count(),
                ];
            })
            ->sortBy([
                ['Nama Kategori', 'asc'],
                ['ID', 'asc'],
            ])
            ->values();
    }

    protected function exportCsv(Collection $rows, string $path): void
    {
        $absolutePath = $this->resolveExportPath($path);
        File::ensureDirectoryExists(dirname($absolutePath));

        $handle = fopen($absolutePath, 'w');

        if ($handle === false) {
            throw new \RuntimeException('Gagal membuat file CSV: ' . $path);
        }

        fputcsv($handle, ['ID', 'Nama Kategori', 'Jumlah Layanan']);

        foreach ($rows as $row) {
            fputcsv($handle, [$row['ID'], $row['Nama Kategori'], $row['Jumlah Layanan']]);
        }

        fclose($handle);
    }

    protected function resolveExportPath(string $path): string
    {
        if (preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1 || str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }

    protected function resolveProviderCategoryId(array $service): ?string
    {
        foreach (['category_id', 'categoryId', 'cat_id', 'catId'] as $key) {
            $value = trim((string) ($service[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function resolveProviderCategoryName(array $service): string
    {
        foreach (['category_name', 'category', 'type'] as $key) {
            $value = trim((string) ($service[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}
