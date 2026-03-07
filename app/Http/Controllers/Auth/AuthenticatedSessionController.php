public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Validasi kredensial email & password
        $request->authenticate();

        // 2. Buat sesi baru untuk keamanan
        $request->session()->regenerate();

        // 3. LOGIKA REDIRECT BERDASARKAN ROLE ID
        $role = $request->user()->role_id;

        if (in_array($role, [1, 2, 3, 4])) {
            // Jika yang login adalah tim Manajemen (Super Admin, Admin, Office, Stok)
            return redirect()->intended(route('admin.dashboard'));
        }

        // Jika yang login adalah Pelanggan / User Biasa (Role 5)
        return redirect()->intended(route('user.dashboard'));
    }