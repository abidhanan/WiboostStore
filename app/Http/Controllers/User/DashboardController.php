<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Promo;
use App\Models\Tutorial;
use App\Services\TripayDepositSyncService;
use App\Support\WiboostCatalog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(TripayDepositSyncService $depositSyncService)
    {
        $depositSyncService->syncForUser((int) Auth::id());

        $user = Auth::user()->fresh();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $catalogSlugs = WiboostCatalog::coreCategorySlugs();
        $categoryOrderSql = WiboostCatalog::categoryOrderSql();

        $categories = Category::whereNull('parent_id')
            ->whereIn('slug', $catalogSlugs)
            ->withCount(['children', 'products'])
            ->orderByRaw($categoryOrderSql)
            ->get();

        $promos = Promo::where('is_active', true)->latest()->get();

        $totalThisMonth = Transaction::where('user_id', $user->id)
                            ->where('payment_status', 'paid')
                            ->where('order_status', '!=', 'failed')
                            ->whereMonth('created_at', $currentMonth)
                            ->whereYear('created_at', $currentYear)
                            ->count();

        $totalSpent = Transaction::where('user_id', $user->id)
                            ->where('payment_status', 'paid')
                            ->where('order_status', '!=', 'failed')
                            ->whereMonth('created_at', $currentMonth)
                            ->whereYear('created_at', $currentYear)
                            ->sum('amount');

        $tutorials = Tutorial::where('is_active', true)->latest()->get();
        $tutorialGroups = $tutorials
            ->groupBy(fn (Tutorial $tutorial) => $tutorial->main_category ?: ($tutorial->category ?: 'Umum'))
            ->map(function ($mainTutorials, $mainCategory) {
                $directTutorials = $mainTutorials
                    ->filter(fn (Tutorial $tutorial) => blank($tutorial->sub_category))
                    ->values();
                $subcategoryTutorials = $mainTutorials
                    ->filter(fn (Tutorial $tutorial) => filled($tutorial->sub_category));

                return [
                    'name' => $mainCategory,
                    'tutorials' => $directTutorials,
                    'subcategories' => $subcategoryTutorials
                        ->groupBy(fn (Tutorial $tutorial) => $tutorial->sub_category)
                        ->map(function ($subTutorials, $subCategory) {
                            $firstTutorial = $subTutorials->first();

                            return [
                                'name' => $subCategory,
                                'image' => $firstTutorial?->sub_category_image ?: $firstTutorial?->image,
                                'tutorials' => $subTutorials->values(),
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        return view('user.dashboard', compact(
            'categories', 
            'promos', 
            'totalThisMonth', 
            'totalSpent', 
            'tutorials',
            'tutorialGroups'
        ));
    }

    public function tutorialCategory(Request $request)
    {
        $mainCategory = trim((string) $request->query('main', ''));
        $subCategory = trim((string) $request->query('sub', ''));

        abort_if($mainCategory === '' || $subCategory === '', 404);

        $tutorials = Tutorial::query()
            ->where('is_active', true)
            ->where(function ($query) use ($mainCategory) {
                $query->where('main_category', $mainCategory)
                    ->orWhere(function ($fallbackQuery) use ($mainCategory) {
                        $fallbackQuery->whereNull('main_category')
                            ->where('category', $mainCategory);
                    });
            })
            ->where(function ($query) use ($subCategory) {
                $query->where('sub_category', $subCategory)
                    ->orWhere(function ($fallbackQuery) use ($subCategory) {
                        $fallbackQuery->whereNull('sub_category')
                            ->where('category', $subCategory);
                    });
            })
            ->latest()
            ->get();

        abort_if($tutorials->isEmpty(), 404);

        $firstTutorial = $tutorials->first();
        $subCategoryImage = $firstTutorial?->sub_category_image ?: $firstTutorial?->image;

        return view('user.tutorial-category', compact(
            'mainCategory',
            'subCategory',
            'subCategoryImage',
            'tutorials'
        ));
    }
}
