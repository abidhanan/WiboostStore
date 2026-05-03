<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RestoreOrderSosmedServices extends Command
{
    protected $signature = 'wiboost:restore-ordersosmed-services
        {--execute : Jalankan restore. Tanpa opsi ini hanya preview.}
        {--resync : Setelah restore, jalankan sync OrderSosmed mode cocok kategori lokal.}';

    protected $description = 'Aktifkan ulang layanan API OrderSosmed yang sempat nonaktif tanpa menyentuh Digiflazz.';

    public function handle(): int
    {
        $baseQuery = $this->baseOrderSosmedQuery();
        $restoreQuery = $this->withoutCommentServices($this->baseOrderSosmedQuery());

        $total = (clone $baseQuery)->count();
        $active = (clone $baseQuery)->where('is_active', true)->count();
        $inactive = (clone $baseQuery)
            ->where(function ($query) {
                $query->where('is_active', false)
                    ->orWhere('status', '!=', 'active')
                    ->orWhereNull('status');
            })
            ->count();
        $commentServices = (clone $this->commentServicesQuery())->count();
        $restorable = (clone $restoreQuery)
            ->where('price', '>', 0)
            ->where(function ($query) {
                $query->where('is_active', false)
                    ->orWhere('status', '!=', 'active')
                    ->orWhereNull('status');
            })
            ->count();

        $this->table(
            ['Total API OrderSosmed', 'Aktif', 'Nonaktif', 'Komentar dilewati', 'Bisa diaktifkan ulang'],
            [[$total, $active, $inactive, $commentServices, $restorable]]
        );

        if (! $this->option('execute')) {
            $this->warn('Preview saja. Tambahkan --execute untuk mengaktifkan ulang layanan API OrderSosmed.');

            return self::SUCCESS;
        }

        $updated = (clone $restoreQuery)
            ->where('price', '>', 0)
            ->update([
                'provider_source' => 'ordersosmed',
                'provider_id' => 'ordersosmed',
                'is_active' => true,
                'status' => 'active',
                'updated_at' => now(),
            ]);

        $this->info("Layanan API OrderSosmed yang diaktifkan ulang: {$updated}");

        if ($this->option('resync')) {
            $this->info('Menjalankan sync ulang OrderSosmed mode cocok kategori lokal...');
            $exitCode = Artisan::call('sync:ordersosmed', [
                '--match-local-categories' => true,
            ]);

            $output = trim((string) Artisan::output());

            if ($output !== '') {
                $this->line($output);
            }

            if ($exitCode !== self::SUCCESS) {
                $this->warn('Restore selesai, tetapi sync ulang OrderSosmed mengembalikan exit code: ' . $exitCode);

                return $exitCode;
            }
        }

        return self::SUCCESS;
    }

    protected function baseOrderSosmedQuery()
    {
        return Product::query()
            ->where(function ($query) {
                $query->where('provider_source', 'ordersosmed')
                    ->orWhere('provider_id', 'ordersosmed');
            })
            ->where('process_type', 'api');
    }

    protected function commentServicesQuery()
    {
        return $this->baseOrderSosmedQuery()
            ->where(function ($query) {
                foreach ($this->commentNeedles() as $needle) {
                    $query->orWhere('name', 'like', '%' . $needle . '%')
                        ->orWhere('description', 'like', '%' . $needle . '%')
                        ->orWhereHas('category', function ($categoryQuery) use ($needle) {
                            $categoryQuery->where('name', 'like', '%' . $needle . '%')
                                ->orWhere('slug', 'like', '%' . $needle . '%');
                        });
                }
            });
    }

    protected function withoutCommentServices($query)
    {
        foreach ($this->commentNeedles() as $needle) {
            $query->where(function ($query) use ($needle) {
                $query->where(function ($query) use ($needle) {
                    $query->whereNull('name')
                        ->orWhere('name', 'not like', '%' . $needle . '%');
                })
                    ->where(function ($query) use ($needle) {
                        $query->whereNull('description')
                            ->orWhere('description', 'not like', '%' . $needle . '%');
                    })
                    ->whereDoesntHave('category', function ($categoryQuery) use ($needle) {
                        $categoryQuery->where('name', 'like', '%' . $needle . '%')
                            ->orWhere('slug', 'like', '%' . $needle . '%');
                    });
            });
        }

        return $query;
    }

    protected function commentNeedles(): array
    {
        return ['comment', 'comments', 'komentar', 'komen', 'reply', 'replies', 'balasan'];
    }
}
