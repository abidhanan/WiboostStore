<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman utama Dashboard Admin.
     */
    public function index()
    {
        // Memanggil file view yang sudah kita buat tadi di resources/views/admin/dashboard.blade.php
        return view('admin.dashboard');
    }
}