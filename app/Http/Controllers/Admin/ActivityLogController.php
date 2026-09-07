<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $filter = (string) $request->query('filter', 'all'); // all | guest | member | proxy | hosting

        $logs = ActivityLog::query()
            ->with(['user', 'geo'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('ip', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%")
                        ->orWhere('route_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('whatsapp', 'like', "%{$search}%");
                        })
                        ->orWhereHas('geo', function ($g) use ($search) {
                            $g->where('country', 'like', "%{$search}%")
                                ->orWhere('region_name', 'like', "%{$search}%")
                                ->orWhere('city', 'like', "%{$search}%")
                                ->orWhere('isp', 'like', "%{$search}%")
                                ->orWhere('asname', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filter === 'guest', fn ($q) => $q->whereNull('user_id'))
            ->when($filter === 'member', fn ($q) => $q->whereNotNull('user_id'))
            ->when($filter === 'proxy', fn ($q) => $q->whereHas('geo', fn ($g) => $g->where('proxy', true)))
            ->when($filter === 'hosting', fn ($q) => $q->whereHas('geo', fn ($g) => $g->where('hosting', true)))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.logs.index', compact('logs', 'search', 'filter'));
    }
}
