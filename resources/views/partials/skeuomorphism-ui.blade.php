<style>
    /* ============ SKEUOMORPHISM GLOBAL — tema warna tetap ============
       Aktif hanya di body.wiboost-skeu pada halaman publik, user, dan admin.
       Aditif: gradasi + bevel + bayangan berlapis, tanpa mengubah warna tema.
       Catatan selektor:
        - [class*="shadow-[#bde0fe]"]  -> semua kartu/box/kontainer/banner bershadow tema
        - [class~="bg-xxx"]            -> cocok token utuh saja (hindari varian hover:/peer-checked:) */

    /* 1) Kedalaman bevel universal untuk semua kartu/box/kontainer (bg TIDAK diubah -> aman semua warna) */
    body.wiboost-skeu [class*="shadow-[#bde0fe]"]:not(input):not(textarea):not(select),
    body.wiboost-skeu .border-4.border-white:not(input):not(textarea):not(select) {
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.55),
            0 2px 3px rgba(90, 118, 200, 0.12),
            0 14px 28px -10px rgba(90, 118, 200, 0.30) !important;
    }

    /* 2) Sheen gradasi khusus kartu putih / terang (lebih spesifik -> menang dari aturan 1) */
    body.wiboost-skeu [class*="shadow-[#bde0fe]"][class~="bg-white"]:not(input):not(textarea):not(select),
    body.wiboost-skeu [class*="shadow-[#bde0fe]"][class~="bg-white/95"],
    body.wiboost-skeu [class*="shadow-[#bde0fe]"][class~="bg-white/90"],
    body.wiboost-skeu [class*="shadow-[#bde0fe]"][class~="bg-[#f4f9ff]"],
    body.wiboost-skeu [class*="shadow-[#bde0fe]"][class~="bg-[#f8faff]"],
    body.wiboost-skeu [class*="shadow-[#bde0fe]"][class~="bg-[#f8fbff]"],
    body.wiboost-skeu .border-4.border-white[class~="bg-white"]:not(input):not(textarea):not(select),
    body.wiboost-skeu .border-4.border-white[class~="bg-white/95"],
    body.wiboost-skeu .border-4.border-white[class~="bg-white/90"],
    body.wiboost-skeu .border-4.border-white[class~="bg-[#f4f9ff]"],
    body.wiboost-skeu .border-4.border-white[class~="bg-[#f8faff]"],
    body.wiboost-skeu .border-4.border-white[class~="bg-[#f8fbff]"] {
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.95),
            inset 0 -3px 6px rgba(163, 187, 251, 0.26),
            0 2px 3px rgba(90, 118, 200, 0.12),
            0 14px 28px -10px rgba(90, 118, 200, 0.30) !important;
    }

    /* 3) Tombol / elemen biru: kaca mengilap */
    body.wiboost-skeu [class~="bg-[#5a76c8]"],
    body.wiboost-skeu [class~="bg-[#4760a9]"],
    body.wiboost-skeu [class~="bg-[#4d66b1]"] {
        background-image: linear-gradient(180deg, #7b9eed 0%, #5a76c8 52%, #4760a9 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.60),
            inset 0 -4px 7px rgba(43, 58, 103, 0.45),
            0 8px 18px rgba(43, 58, 103, 0.35) !important;
        transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
    }
    body.wiboost-skeu [class~="bg-[#5a76c8]"]:hover,
    body.wiboost-skeu [class~="bg-[#4760a9]"]:hover { filter: brightness(1.05); }
    body.wiboost-skeu [class~="bg-[#5a76c8]"]:active,
    body.wiboost-skeu [class~="bg-[#4760a9]"]:active { transform: translateY(1px); }

    /* 4) Tombol / elemen teal: kaca mengilap */
    body.wiboost-skeu [class~="bg-[#4bc6b9]"],
    body.wiboost-skeu [class~="bg-[#3ba398]"] {
        background-image: linear-gradient(180deg, #6fe0d3 0%, #4bc6b9 52%, #3ba398 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.60),
            inset 0 -4px 7px rgba(24, 110, 100, 0.42),
            0 8px 18px rgba(24, 110, 100, 0.35) !important;
    }

    /* 4b) Tombol merah (danger): kaca gelap-terang */
    body.wiboost-skeu [class~="bg-[#ff6b6b]"],
    body.wiboost-skeu [class~="bg-[#ff4d4d]"],
    body.wiboost-skeu [class~="bg-[#e55050]"] {
        background-image: linear-gradient(180deg, #ff9b9b 0%, #ff6b6b 52%, #e04f4f 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.55),
            inset 0 -4px 7px rgba(150, 40, 40, 0.40),
            0 8px 18px rgba(150, 40, 40, 0.32) !important;
    }
    /* 4c) Tombol hijau (WhatsApp): kaca gelap-terang */
    body.wiboost-skeu [class~="bg-[#25D366]"],
    body.wiboost-skeu [class~="bg-[#1fb458]"] {
        background-image: linear-gradient(180deg, #5be591 0%, #25D366 52%, #1fb458 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.55),
            inset 0 -4px 7px rgba(16, 90, 50, 0.40),
            0 8px 18px rgba(16, 90, 50, 0.30) !important;
    }

    /* 5) Input / textarea / select: sumur cekung (inset) khas skeuomorphism */
    body.wiboost-skeu input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="hidden"]),
    body.wiboost-skeu textarea,
    body.wiboost-skeu select {
        box-shadow:
            inset 0 2px 5px rgba(163, 187, 251, 0.28),
            inset 0 1px 2px rgba(90, 118, 200, 0.14) !important;
    }

    /* 6) Pill / badge (rounded-full ber-border): highlight + bayangan lembut.
          Memakai .rounded-full.border-2 (BUKAN .border-2.border-white) supaya tidak mengenai
          wrapper transparan md:border-0 -> mencegah "kotak hantu" di belakang label & harga. */
    body.wiboost-skeu .rounded-full.border-2:not(input):not(textarea):not(select) {
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.70),
            0 4px 10px -3px rgba(90, 118, 200, 0.22) !important;
    }

    /* 7) Navbar user + panel sidebar: kaca mengilap */
    body.wiboost-skeu .wiboost-user-navbar-card,
    body.wiboost-skeu .wiboost-user-sidebar > div {
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.95),
            0 12px 28px -12px rgba(90, 118, 200, 0.32) !important;
    }

    /* 8) Satu model footer untuk halaman publik, user, dan admin. */
    body.wiboost-skeu .wiboost-site-footer {
        border-top: 0 !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #eaf2ff 100%) !important;
        box-shadow: none !important;
    }
    body.wiboost-skeu .wiboost-site-footer .footer-skeu-divider {
        border-top: 2px solid #dce7f8 !important;
        box-shadow: none !important;
    }
    body.wiboost-skeu .wiboost-site-footer .footer-description {
        width: auto;
        max-width: 22rem;
        font-size: 12px;
        line-height: 1.5;
        transform: none;
    }
    @media (min-width: 640px) {
        body.wiboost-skeu .wiboost-site-footer .footer-description {
            max-width: 36rem;
            font-size: 12px;
        }
    }
    @media (min-width: 1024px) {
        body.wiboost-skeu .wiboost-site-footer .footer-description {
            width: max-content;
            max-width: none;
            font-size: 14px;
            white-space: nowrap;
        }
    }
    @media (max-width: 639px) {
        body.wiboost-skeu .wiboost-site-footer > div {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
        body.wiboost-skeu .wiboost-site-footer .footer-legal-links {
            display: flex !important;
            flex-wrap: nowrap !important;
            justify-content: center !important;
            gap: 0.25rem !important;
        }
        body.wiboost-skeu .wiboost-site-footer .footer-legal-link {
            flex: 0 0 auto !important;
            width: auto !important;
            min-height: 0;
            padding: 0.375rem 0.75rem !important;
            font-size: 9px !important;
        }
    }
    body.wiboost-skeu .wiboost-site-footer .footer-social {
        position: relative;
        overflow: hidden;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.9),
            inset 0 -3px 5px rgba(163, 187, 251, 0.30),
            0 6px 13px rgba(90, 118, 200, 0.24) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease;
    }
    body.wiboost-skeu .wiboost-site-footer .footer-legal-link {
        background-image: linear-gradient(180deg, #ffffff 0%, #e8f0ff 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.95),
            inset 0 -2px 4px rgba(163, 187, 251, 0.30),
            0 4px 9px -2px rgba(90, 118, 200, 0.22) !important;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    body.wiboost-skeu .wiboost-site-footer .footer-legal-link:hover {
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.95),
            inset 0 -2px 4px rgba(163, 187, 251, 0.32),
            0 7px 14px -3px rgba(90, 118, 200, 0.28) !important;
    }

    /* 9) Banner promo & display card: kedalaman mengilap TANPA mengubah bg dinamis */
    body.wiboost-skeu .promo-slide,
    body.wiboost-skeu .wiboost-display-card {
        box-shadow:
            inset 0 2px 0 rgba(255, 255, 255, 0.35),
            0 16px 34px -12px rgba(43, 58, 103, 0.40) !important;
    }

    /* 10) Modal detail (riwayat/pesanan user) */
    body.wiboost-skeu .history-modal-card,
    body.wiboost-skeu .admin-detail-card {
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.95),
            0 24px 50px -12px rgba(43, 58, 103, 0.45) !important;
    }

    /* 11) Node langkah pesanan (order flow) */
    body.wiboost-skeu .order-step-node {
        background-image: linear-gradient(180deg, #7b9eed 0%, #5a76c8 52%, #4760a9 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.60),
            inset 0 -4px 7px rgba(43, 58, 103, 0.45),
            0 8px 16px rgba(43, 58, 103, 0.30) !important;
    }

    /* 12) Filter tutorial: warna dasar SOLID supaya SELALU terbaca (tidak tertimpa gloss putih
           aturan 2 / tidak tertutup sheen). Non-aktif = putih + teks biru; aktif = biru + teks
           putih. Bevel hanya lewat box-shadow. Spesifisitas (0,3,1) + urutan setelah aturan 2. */
    body.wiboost-skeu .tut-filter-btn.tut-filter-btn:not(.is-active) {
        background-image: none !important;
        background-color: #ffffff !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.9),
            0 4px 11px -3px rgba(90, 118, 200, 0.22) !important;
    }
    body.wiboost-skeu .tut-filter-btn.tut-filter-btn.is-active {
        background-image: none !important;
        background-color: #5a76c8 !important;
        color: #ffffff !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.5),
            0 6px 15px -4px rgba(43, 58, 103, 0.35) !important;
    }

    /* 14) Sidebar: link profil & membership */
    body.wiboost-skeu .wiboost-sidebar-profile-link {
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.95),
            inset 0 -2px 4px rgba(163, 187, 251, 0.25),
            0 6px 14px -4px rgba(90, 118, 200, 0.22) !important;
    }
    body.wiboost-skeu .wiboost-sidebar-membership-link {
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.45),
            inset 0 -4px 7px rgba(80, 40, 160, 0.35),
            0 12px 22px -6px rgba(139, 92, 246, 0.38) !important;
    }

    /* 16) Halaman profil: bingkai foto + tombol edit */
    body.wiboost-skeu .profile-photo-frame {
        box-shadow:
            inset 0 2px 4px rgba(255, 255, 255, 0.85),
            0 16px 35px -8px rgba(189, 224, 254, 0.55) !important;
    }
    body.wiboost-skeu .profile-photo-edit-button {
        background-image: linear-gradient(180deg, #7b9eed 0%, #5a76c8 52%, #4760a9 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.55),
            inset 0 -3px 5px rgba(43, 58, 103, 0.40),
            0 10px 20px -4px rgba(43, 58, 103, 0.30) !important;
    }

    /* 17) Tombol salin (copy) */
    body.wiboost-skeu [title="Salin"] {
        background-image: linear-gradient(180deg, #fff6e0 0%, #ffedc2 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.85),
            inset 0 -2px 4px rgba(214, 158, 46, 0.26),
            0 4px 10px -3px rgba(214, 158, 46, 0.24) !important;
    }

    /* 18) Halaman legal: depth skeu tanpa mengubah bg */
    body.wiboost-skeu .wiboost-legal-hero,
    body.wiboost-skeu .wiboost-legal-card,
    body.wiboost-skeu .wiboost-legal-update {
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.85),
            inset 0 -3px 6px rgba(163, 187, 251, 0.20),
            0 14px 28px -10px rgba(90, 118, 200, 0.26) !important;
    }

    /* 19) Kartu "pilih riwayat/kendala" (halaman bantuan) */
    body.wiboost-skeu [data-report-record-card],
    body.wiboost-skeu .report-type-button {
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.85),
            0 8px 18px -6px rgba(90, 118, 200, 0.24) !important;
    }

    /* 20) Tombol modal detail pesanan (spesifisitas 0,4,1 -> menang dari aturan 1 & 2):
           - X (merah): datar + bevel, teks terbaca.
           - Tutup Detail (biru, warna DITUKAR jadi primary): kaca mengilap penuh. */
    body.wiboost-skeu .history-modal-card [onclick*="closeModal"][class~="bg-[#ffe5e5]"] {
        background-image: none !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.8),
            0 5px 13px -4px rgba(90, 118, 200, 0.25) !important;
    }
    body.wiboost-skeu .history-modal-card [onclick*="closeModal"][class~="bg-[#5a76c8]"] {
        background-image: linear-gradient(180deg, #7b9eed 0%, #5a76c8 52%, #4760a9 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.55),
            inset 0 -4px 7px rgba(43, 58, 103, 0.42),
            0 10px 22px -6px rgba(43, 58, 103, 0.38) !important;
    }

    /* 20b) HEADER & FOOTER modal detail pesanan -> PANEL SKEU FULL-BLEED yang MENUTUPI PENUH tepi card.
            Header menutup tepi ATAS + kiri/kanan; footer menutup tepi BAWAH + kiri/kanan.
            Margin negatif meng-cancel padding card (p-4 mobile / md:p-5 desktop); align-self:stretch
            + width:auto agar panel melebar penuh; sudut otomatis di-clip overflow-hidden+rounded card. */
    body.wiboost-skeu .history-modal-card [class*="border-b-4"][class*="border-dashed"] {
        border-style: solid !important;
        border-color: #ffffff !important;
        border-radius: 0 !important;
        align-self: stretch !important;
        width: auto !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.95),
            inset 0 -3px 6px rgba(163, 187, 251, 0.20) !important;
        margin: -0.85rem -0.85rem 1rem -0.85rem !important;
        padding: 0.85rem 0.85rem 0.8rem 0.85rem !important;
    }
    body.wiboost-skeu .history-modal-card [class*="border-t-4"][class*="border-dashed"] {
        border-style: solid !important;
        border-color: #ffffff !important;
        border-radius: 0 !important;
        align-self: stretch !important;
        width: auto !important;
        background-image: linear-gradient(180deg, #eef4ff 0%, #ffffff 100%) !important;
        box-shadow:
            inset 0 -1px 0 rgba(255, 255, 255, 0.95),
            inset 0 3px 6px rgba(163, 187, 251, 0.20) !important;
        margin: 1rem -0.85rem -0.85rem -0.85rem !important;
        padding: 0.8rem 0.85rem 0.85rem 0.85rem !important;
    }
    @media (min-width: 768px) {
        body.wiboost-skeu .history-modal-card [class*="border-b-4"][class*="border-dashed"] {
            margin: -1.25rem -1.25rem 1rem -1.25rem !important;
            padding: 1.25rem 1.25rem 0.9rem 1.25rem !important;
        }
        body.wiboost-skeu .history-modal-card [class*="border-t-4"][class*="border-dashed"] {
            margin: 1rem -1.25rem -1.25rem -1.25rem !important;
            padding: 1rem 1.25rem 1.25rem 1.25rem !important;
        }
    }

    /* 20c) Zona harga kartu produk -> PANEL SKEU FULL-BLEED menutup tepi BAWAH + kiri/kanan card.
            Mode normal (card p-6) & compact (card p-2 / sm:p-4) ditangani terpisah. Pada mode normal
            margin-top: auto DIPERTAHANKAN agar harga tetap menempel di bawah.
            Diikat ke kelas .wiboost-price-zone (bukan selektor [border-t-2] luas) agar tidak bocor
            ke elemen border-t-2 dashed lain di masa depan. */
    body.wiboost-skeu .wiboost-price-zone:not(.wiboost-compact-price-zone) {
        border-style: solid !important;
        border-color: #ffffff !important;
        border-radius: 0 !important;
        align-self: stretch !important;
        width: auto !important;
        background-image: linear-gradient(180deg, #eef4ff 0%, #ffffff 100%) !important;
        box-shadow:
            inset 0 -1px 0 rgba(255, 255, 255, 0.95),
            inset 0 2px 5px rgba(163, 187, 251, 0.22),
            0 -9px 14px -11px rgba(90, 118, 200, 0.30) !important;
        margin: auto -1.5rem -1.5rem -1.5rem !important;
        padding: 0.9rem 1.5rem 1.2rem 1.5rem !important;
    }
    body.wiboost-skeu .wiboost-compact-price-zone[class*="border-dashed"] {
        border-style: solid !important;
        border-color: #ffffff !important;
        border-radius: 0 !important;
        align-self: stretch !important;
        width: auto !important;
        background-image: linear-gradient(180deg, #eef4ff 0%, #ffffff 100%) !important;
        box-shadow:
            inset 0 -1px 0 rgba(255, 255, 255, 0.95),
            inset 0 2px 4px rgba(163, 187, 251, 0.22),
            0 -7px 12px -10px rgba(90, 118, 200, 0.28) !important;
        margin: 0.4rem -0.5rem -0.5rem -0.5rem !important;
        padding: 0.45rem 0.5rem 0.55rem 0.5rem !important;
    }
    @media (min-width: 640px) {
        body.wiboost-skeu .wiboost-compact-price-zone[class*="border-dashed"] {
            margin: 0.5rem -1rem -1rem -1rem !important;
            padding: 0.5rem 1rem 0.7rem 1rem !important;
        }
    }

    /* 20d) Garis "Detail Pesanan" di form order Suntik Sosmed -> PANEL SKEU FULL-BLEED menutup PENUH
            tepi ATAS + kiri/kanan form checkout. Form padding p-6 (mobile) / md:p-10 (desktop);
            overflow-hidden + rounded milik form meng-clip sudut. Spesifisitas (0,3,1) > aturan 27. */
    body.wiboost-skeu .wiboost-order-detail-divider[class*="border-dashed"] {
        border-style: solid !important;
        border-color: #ffffff !important;
        border-radius: 0 !important;
        align-self: stretch !important;
        width: auto !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.95),
            inset 0 -3px 6px rgba(163, 187, 251, 0.20) !important;
        margin: -1.5rem -1.5rem 1.5rem -1.5rem !important;
        padding: 1.5rem 1.5rem 1rem 1.5rem !important;
    }
    @media (min-width: 768px) {
        body.wiboost-skeu .wiboost-order-detail-divider[class*="border-dashed"] {
            margin: -2.5rem -2.5rem 1.5rem -2.5rem !important;
            padding: 2.5rem 2.5rem 1.1rem 2.5rem !important;
        }
    }

    /* 27) FALLBACK garis putus-putus umum -> pemisah solid bersih tanpa garis ganda.
           Spesifisitas rendah (0,2,1) & ditaruh SEBELUM aturan sidebar/modal/harga, sehingga:
           - aturan spesifik (20b modal, 20c harga, 24 sidebar desktop) menang via spesifisitas;
           - header/footer sidebar MOBILE (0,2,1) menang via urutan sumber (didefinisikan setelah ini). */
    body.wiboost-skeu [class*="border-dashed"] {
        border-style: solid !important;
        border-color: #ffffff !important;
        box-shadow: none !important;
    }
    body.wiboost-skeu table :is(th, td)[class*="border-dashed"],
    body.wiboost-skeu table tr[class*="border-dashed"] > :is(th, td) {
        border-color: #e5edfb !important;
        box-shadow: none !important;
    }

    /* 24) HEADER sidebar (desktop + mobile): panel skeu, garis putih di BAWAH, bayangan ke bawah */
    body.wiboost-skeu .wiboost-user-sidebar [class*="border-b-4"][class*="border-dashed"],
    body.wiboost-skeu .wiboost-admin-sidebar [class*="border-b-4"][class*="border-dashed"],
    body.wiboost-skeu .wiboost-mobile-sidebar-header {
        border-style: solid !important;
        border-color: #ffffff !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.95),
            0 9px 16px -10px rgba(90, 118, 200, 0.32) !important;
    }
    /* 24b) FOOTER sidebar (desktop + mobile): panel skeu, garis putih di ATAS, bayangan ke atas */
    body.wiboost-skeu .wiboost-user-sidebar [class*="border-t-4"][class*="border-dashed"],
    body.wiboost-skeu .wiboost-admin-sidebar [class*="border-t-4"][class*="border-dashed"],
    body.wiboost-skeu .wiboost-mobile-sidebar-footer {
        border-style: solid !important;
        border-color: #ffffff !important;
        background-image: linear-gradient(180deg, #eef4ff 0%, #ffffff 100%) !important;
        box-shadow:
            inset 0 -1px 0 rgba(255, 255, 255, 0.95),
            0 -9px 16px -10px rgba(90, 118, 200, 0.32) !important;
    }

    /* 24c) FULL-BLEED menu mobile: header & footer menempel penuh ke tepi panel (panel padding 1.25rem)
            supaya rapi seperti bar, bukan kartu mengambang dengan celah di pinggir. */
    body.wiboost-skeu .wiboost-mobile-sidebar-header {
        margin: -1.25rem -1.25rem 0 -1.25rem !important;
        padding: 1.25rem 1.25rem 0.9rem 1.25rem !important;
    }
    body.wiboost-skeu .wiboost-mobile-sidebar-footer {
        margin: 0 -1.25rem -1.25rem -1.25rem !important;
        padding: 0.9rem 1.25rem 1.25rem 1.25rem !important;
    }

    /* 25) Dropdown order "Suntik Sosmed": jarak antar opsi biar tidak mepet.
           Gloss + bevel skeu (trigger/panel/opsi) ditangani langsung di CSS halaman order
           supaya spesifisitas pasti menang atas aturan lokal :not(.is-selected) !important. */
    body.wiboost-skeu .wiboost-service-option {
        margin-bottom: 0.4rem !important;
    }

    /* 26) Jarak antar item dropdown skeu (jangan mepet, seperti menu sidebar) */
    body.wiboost-skeu .wiboost-dropdown-item {
        margin-bottom: 0.45rem !important;
    }

    /* 21) Data Riwayat Komisi (reseller): tiap baris jadi kartu skeu terang */
    body.wiboost-skeu .reseller-scroll-panel > div {
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.95),
            inset 0 -2px 4px rgba(163, 187, 251, 0.22),
            0 6px 14px -5px rgba(90, 118, 200, 0.22) !important;
    }

    /* 22) Baris info / benefit (membership & reseller): p ber-rounded light jadi skeu */
    body.wiboost-skeu p[class~="rounded-[1.25rem]"][class~="bg-[#f4f9ff]"],
    body.wiboost-skeu p[class~="rounded-[1.25rem]"][class~="bg-[#f8fbff]"],
    body.wiboost-skeu p[class~="rounded-[1rem]"][class~="bg-[#f4f9ff]"] {
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.95),
            inset 0 -2px 4px rgba(163, 187, 251, 0.22),
            0 5px 12px -4px rgba(90, 118, 200, 0.20) !important;
    }

    /* 23) Item menu dropdown (profil/keluar & dropdown lain) jadi chip skeu (hover tetap hidup) */
    body.wiboost-skeu .wiboost-dropdown-item {
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.9),
            0 4px 10px -4px rgba(90, 118, 200, 0.22) !important;
    }

    /* 28) Halaman QRIS: kotak "isi" info (biru tua #4965b5) jadi SUMUR skeu cekung (teks putih
           tetap terbaca), badge status (label) jadi pill mengilap, bingkai QR jadi sumur lembut. */
    body.wiboost-skeu [class~="bg-[#4965b5]"] {
        background-image: linear-gradient(180deg, #41598c 0%, #4965b5 100%) !important;
        box-shadow:
            inset 0 2px 6px rgba(18, 28, 58, 0.45),
            inset 0 1px 0 rgba(255, 255, 255, 0.18),
            0 6px 14px -8px rgba(43, 58, 103, 0.30) !important;
    }
    body.wiboost-skeu .qris-status-badge {
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.60),
            inset 0 -2px 4px rgba(0, 0, 0, 0.14),
            0 4px 10px -3px rgba(43, 58, 103, 0.30) !important;
    }
    body.wiboost-skeu .qris-code-frame {
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.95),
            0 10px 22px -8px rgba(90, 118, 200, 0.28) !important;
    }

    /* 29) Pita diskon (corner ribbon) -> banner SKEU mengilap: gloss gradient + highlight atas +
           bevel bawah. Warna tiap varian dipertahankan (biru default, merah sold-out, ungu member).
           Teks putih tetap terbaca. */
    body.wiboost-skeu .discount-corner-ribbon__text {
        background-image: linear-gradient(180deg, #7b9eed 0%, #5a76c8 52%, #4760a9 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.55),
            inset 0 -3px 5px rgba(43, 58, 103, 0.40),
            0 10px 22px rgba(43, 58, 103, 0.30) !important;
    }
    body.wiboost-skeu .discount-corner-ribbon--sold-out .discount-corner-ribbon__text {
        background-image: linear-gradient(180deg, #ff9b9b 0%, #ff6b6b 52%, #e04f4f 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.50),
            inset 0 -3px 5px rgba(150, 40, 40, 0.40),
            0 10px 22px rgba(255, 107, 107, 0.30) !important;
    }
    body.wiboost-skeu .discount-corner-ribbon--member .discount-corner-ribbon__text {
        background-image: linear-gradient(180deg, #a78bfa 0%, #8b5cf6 52%, #6d28d9 100%) !important;
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.50),
            inset 0 -3px 5px rgba(80, 40, 160, 0.42),
            0 10px 22px rgba(139, 92, 246, 0.32) !important;
    }

    /* 30) MODEL TOMBOL GELAP-TERANG seperti welcome (.skeu-primary): glossy sheen ::before di separuh
           atas tombol FILLED (SEMUA warna: biru, teal, merah, hijau). Scope button/a; KECUALIKAN
           .absolute/.fixed supaya tombol ber-posisi (mis. toggle "lihat password") TIDAK bergeser. */
    body.wiboost-skeu :is(button, a)[class~="bg-[#5a76c8]"]:not(.absolute):not(.fixed),
    body.wiboost-skeu :is(button, a)[class~="bg-[#4760a9]"]:not(.absolute):not(.fixed),
    body.wiboost-skeu :is(button, a)[class~="bg-[#4d66b1]"]:not(.absolute):not(.fixed),
    body.wiboost-skeu :is(button, a)[class~="bg-[#4bc6b9]"]:not(.absolute):not(.fixed),
    body.wiboost-skeu :is(button, a)[class~="bg-[#3ba398]"]:not(.absolute):not(.fixed),
    body.wiboost-skeu :is(button, a)[class~="bg-[#ff6b6b]"]:not(.absolute):not(.fixed),
    body.wiboost-skeu :is(button, a)[class~="bg-[#ff4d4d]"]:not(.absolute):not(.fixed),
    body.wiboost-skeu :is(button, a)[class~="bg-[#e55050]"]:not(.absolute):not(.fixed),
    body.wiboost-skeu :is(button, a)[class~="bg-[#25D366]"]:not(.absolute):not(.fixed),
    body.wiboost-skeu :is(button, a)[class~="bg-[#1fb458]"]:not(.absolute):not(.fixed) {
        position: relative;
        overflow: hidden;
    }
    body.wiboost-skeu :is(button, a)[class~="bg-[#5a76c8]"]:not(.absolute):not(.fixed)::before,
    body.wiboost-skeu :is(button, a)[class~="bg-[#4760a9]"]:not(.absolute):not(.fixed)::before,
    body.wiboost-skeu :is(button, a)[class~="bg-[#4d66b1]"]:not(.absolute):not(.fixed)::before,
    body.wiboost-skeu :is(button, a)[class~="bg-[#4bc6b9]"]:not(.absolute):not(.fixed)::before,
    body.wiboost-skeu :is(button, a)[class~="bg-[#3ba398]"]:not(.absolute):not(.fixed)::before,
    body.wiboost-skeu :is(button, a)[class~="bg-[#ff6b6b]"]:not(.absolute):not(.fixed)::before,
    body.wiboost-skeu :is(button, a)[class~="bg-[#ff4d4d]"]:not(.absolute):not(.fixed)::before,
    body.wiboost-skeu :is(button, a)[class~="bg-[#e55050]"]:not(.absolute):not(.fixed)::before,
    body.wiboost-skeu :is(button, a)[class~="bg-[#25D366]"]:not(.absolute):not(.fixed)::before,
    body.wiboost-skeu :is(button, a)[class~="bg-[#1fb458]"]:not(.absolute):not(.fixed)::before {
        content: "";
        position: absolute;
        left: 0; right: 0; top: 0;
        height: 50%;
        border-radius: inherit;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.45), rgba(255, 255, 255, 0.05) 100%);
        pointer-events: none;
    }

    /* 31) MODEL TOMBOL PILL terang seperti welcome (.skeu-pill): sheen lembut; teks berwarna di tengah
           tetap terbaca. KECUALIKAN .absolute/.fixed (toggle password tak geser) & tombol filled. */
    body.wiboost-skeu :is(button, a).rounded-full.border-2:not(.absolute):not(.fixed):not([class~="bg-[#5a76c8]"]):not([class~="bg-[#4760a9]"]):not([class~="bg-[#4bc6b9]"]):not([class~="bg-[#3ba398]"]):not([class~="bg-[#ff6b6b]"]):not([class~="bg-[#25D366]"]) {
        position: relative;
        overflow: hidden;
    }
    body.wiboost-skeu :is(button, a).rounded-full.border-2:not(.absolute):not(.fixed):not([class~="bg-[#5a76c8]"]):not([class~="bg-[#4760a9]"]):not([class~="bg-[#4bc6b9]"]):not([class~="bg-[#3ba398]"]):not([class~="bg-[#ff6b6b]"]):not([class~="bg-[#25D366]"])::before {
        content: "";
        position: absolute;
        left: 0; right: 0; top: 0;
        height: 52%;
        border-radius: inherit;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0) 100%);
        pointer-events: none;
    }

    /* 32) Permukaan warna terang: rona asli dipertahankan dengan highlight dan bevel. */
    body.wiboost-skeu :is([class~="bg-[#f0f5ff]"], [class~="bg-[#f4f9ff]"], [class~="bg-[#f8faff]"], [class~="bg-[#f8fbff]"], [class~="bg-[#eef4ff]"], [class~="bg-[#eef6ff]"], [class~="bg-[#e8f1ff]"], [class~="bg-[#e8f2ff]"], [class~="bg-[#dcecff]"], [class~="bg-[#dbeafe]"], [class~="bg-blue-50"]):not(input):not(textarea):not(select) {
        background-image: linear-gradient(180deg, #ffffff 0%, #e9f1ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .95), inset 0 -2px 4px rgba(143, 170, 243, .22), 0 5px 12px -6px rgba(90, 118, 200, .28) !important;
    }
    body.wiboost-skeu :is([class~="bg-[#e0fbfc]"], [class~="bg-[#e8fbff]"], [class~="bg-[#d4f7f4]"], [class~="bg-[#e6fff7]"], [class~="bg-[#f6fffb]"]):not(input):not(textarea):not(select) {
        background-image: linear-gradient(180deg, #f8ffff 0%, #d4f7f4 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .95), inset 0 -2px 4px rgba(75, 198, 185, .22), 0 5px 12px -6px rgba(59, 163, 152, .28) !important;
    }
    body.wiboost-skeu :is([class~="bg-[#fff1cc]"], [class~="bg-[#fff1d6]"], [class~="bg-[#fff5d6]"], [class~="bg-[#fff5eb]"], [class~="bg-[#fff7d6]"], [class~="bg-[#fff7e0]"], [class~="bg-[#fffcf0]"], [class~="bg-[#fffdf7]"]):not(input):not(textarea):not(select) {
        background-image: linear-gradient(180deg, #fffef8 0%, #fff0c7 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .95), inset 0 -2px 4px rgba(217, 155, 22, .22), 0 5px 12px -6px rgba(217, 155, 22, .28) !important;
    }
    body.wiboost-skeu :is([class~="bg-[#ffe5e5]"], [class~="bg-[#ffcaca]"], [class~="bg-[#ffcccc]"], [class~="bg-[#fff1f1]"], [class~="bg-[#fff3f3]"], [class~="bg-[#fff5f5]"], [class~="bg-[#ffeaf3]"], [class~="bg-[#ffeef2]"], [class~="bg-[#fff7fb]"]):not(input):not(textarea):not(select) {
        background-image: linear-gradient(180deg, #fffafa 0%, #ffe0e5 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .95), inset 0 -2px 4px rgba(255, 107, 107, .20), 0 5px 12px -6px rgba(229, 80, 80, .27) !important;
    }
    body.wiboost-skeu :is([class~="bg-[#ede9fe]"], [class~="bg-[#f5f3ff]"]):not(input):not(textarea):not(select) {
        background-image: linear-gradient(180deg, #ffffff 0%, #e9e2ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .95), inset 0 -2px 4px rgba(139, 92, 246, .20), 0 5px 12px -6px rgba(109, 40, 217, .27) !important;
    }

    /* 33) Warna tombol/label tambahan pada admin memakai gelap-terang yang sama. */
    body.wiboost-skeu :is([class~="bg-[#6f8ddc]"], [class~="bg-[#7b9eed]"], [class~="bg-[#8faaf3]"]) {
        background-image: linear-gradient(180deg, #9eb8f7 0%, #6f8ddc 55%, #526db9 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .58), inset 0 -4px 7px rgba(43, 58, 103, .36), 0 8px 18px rgba(43, 58, 103, .28) !important;
    }
    body.wiboost-skeu :is([class~="bg-[#fbbf24]"], [class~="bg-[#d99b16]"], [class~="bg-[#ffe59b]"]) {
        background-image: linear-gradient(180deg, #ffe59b 0%, #fbbf24 55%, #d99b16 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .58), inset 0 -4px 7px rgba(140, 91, 8, .32), 0 8px 18px rgba(140, 91, 8, .24) !important;
    }
    body.wiboost-skeu :is([class~="bg-amber-50"], [class~="bg-amber-100"], [class~="bg-amber-200"]):not(input):not(textarea):not(select) {
        background-image: linear-gradient(180deg, #fffef8 0%, #ffedb4 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .95), inset 0 -2px 4px rgba(217, 155, 22, .22), 0 5px 12px -6px rgba(217, 155, 22, .28) !important;
    }
    body.wiboost-skeu [class~="bg-emerald-50"]:not(input):not(textarea):not(select) {
        background-image: linear-gradient(180deg, #ffffff 0%, #e5f7f2 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .95), inset 0 -2px 4px rgba(75, 198, 185, .19), 0 5px 12px -6px rgba(59, 163, 152, .25) !important;
    }
    body.wiboost-skeu [class~="bg-[#bde0fe]"]:not(input):not(textarea):not(select) {
        background-image: linear-gradient(180deg, #e8f5ff 0%, #bde0fe 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .88), inset 0 -3px 5px rgba(90, 118, 200, .22), 0 6px 14px -7px rgba(90, 118, 200, .30) !important;
    }
    body.wiboost-skeu :is([class~="bg-amber-500"], [class~="bg-amber-600"]) {
        background-image: linear-gradient(180deg, #ffd56a 0%, #f59e0b 55%, #c77708 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .56), inset 0 -4px 7px rgba(120, 70, 6, .34), 0 8px 18px rgba(140, 91, 8, .26) !important;
    }
    body.wiboost-skeu [class~="bg-emerald-400"] {
        background-image: linear-gradient(180deg, #6ee7b7 0%, #34d399 55%, #169873 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .56), inset 0 -4px 7px rgba(10, 90, 65, .34), 0 8px 18px rgba(10, 90, 65, .26) !important;
    }
    body.wiboost-skeu [class~="bg-[#2b3a67]"] {
        background-image: linear-gradient(180deg, #435483 0%, #2b3a67 58%, #1d294e 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .20), inset 0 -4px 7px rgba(13, 21, 44, .45), 0 9px 20px rgba(43, 58, 103, .34) !important;
    }

    /* 34) Cangkang admin, header panel, dan isi detail mengikuti model welcome. */
    body.wiboost-skeu.wiboost-admin-shell .wiboost-admin-navbar-card,
    body.wiboost-skeu.wiboost-admin-shell .wiboost-admin-sidebar > div,
    body.wiboost-skeu.wiboost-admin-shell .wiboost-admin-mobile-menu-panel {
        background-image: linear-gradient(180deg, rgba(255, 255, 255, .98) 0%, rgba(234, 242, 255, .96) 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), 0 12px 28px -12px rgba(90, 118, 200, .34) !important;
    }
    body.wiboost-skeu.wiboost-admin-shell main div[class*="border-b-4"][class*="border-dashed"][class*="bg-"],
    body.wiboost-skeu.wiboost-admin-shell main header[class*="border-b"][class*="bg-"] {
        border-style: solid !important;
        border-color: #ffffff !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #eaf2ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), inset 0 -3px 6px rgba(163, 187, 251, .22), 0 7px 16px -12px rgba(90, 118, 200, .42) !important;
    }
    body.wiboost-skeu.wiboost-admin-shell .admin-detail-card {
        border: 4px solid #ffffff !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), 0 24px 50px -12px rgba(43, 58, 103, .45) !important;
    }
    body.wiboost-skeu.wiboost-admin-shell .admin-detail-header {
        border: 0 !important;
        border-bottom: 3px solid #ffffff !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #eaf2ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), inset 0 -3px 6px rgba(163, 187, 251, .22), 0 8px 16px -12px rgba(90, 118, 200, .38) !important;
    }
    body.wiboost-skeu.wiboost-admin-shell .admin-detail-panel,
    body.wiboost-skeu.wiboost-admin-shell .admin-detail-subfield {
        border-color: #ffffff !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #edf4ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), inset 0 -2px 5px rgba(163, 187, 251, .20), 0 8px 18px -10px rgba(90, 118, 200, .30) !important;
    }
    body.wiboost-skeu.wiboost-admin-shell .admin-detail-panel-soft {
        border-color: #ffffff !important;
        background-image: linear-gradient(180deg, #fffef9 0%, #fff0cd 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), inset 0 -2px 5px rgba(217, 155, 22, .19), 0 8px 18px -10px rgba(217, 155, 22, .28) !important;
    }

    body.wiboost-skeu.wiboost-admin-shell .wiboost-admin-footer {
        flex: 0 0 auto;
        width: auto;
    }

    /* 35) Panel pembuka/penutup dibuat utuh, bukan garis tipis yang tersisa dari desain lama. */
    body.wiboost-skeu main :is(header, footer)[class*="border-b"],
    body.wiboost-skeu main :is(header, footer)[class*="border-t"],
    body.wiboost-skeu main [data-panel-header],
    body.wiboost-skeu main [data-panel-footer] {
        border-style: solid !important;
        border-color: #ffffff !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #eaf2ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), inset 0 -3px 6px rgba(163, 187, 251, .20) !important;
    }
    body.wiboost-skeu main :is(.history-modal-card, .admin-detail-card) > :first-child[class*="border-b"],
    body.wiboost-skeu main :is(.history-modal-card, .admin-detail-card) > :last-child[class*="border-t"] {
        align-self: stretch !important;
        width: auto !important;
        border-radius: 0 !important;
    }

    /* 36) Semua dropdown custom dan native memakai kedalaman skeu yang sama. */
    body.wiboost-skeu :is(
        .admin-theme-select-button,
        .admin-category-theme-select-button,
        .admin-product-theme-select-button,
        .admin-promo-theme-select-button,
        .admin-user-role-select-button,
        .wiboost-answer-type-button,
        .wiboost-public-form-select-button,
        .wiboost-service-trigger,
        [aria-haspopup="listbox"]
    ) {
        border-color: #ffffff !important;
        background-color: #f4f9ff !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #e8f0ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), inset 0 -3px 6px rgba(143, 170, 243, .24), 0 8px 18px -8px rgba(90, 118, 200, .34) !important;
    }
    body.wiboost-skeu :is(
        .admin-theme-select-menu,
        .admin-category-theme-select-menu,
        .admin-product-theme-select-menu,
        .admin-promo-theme-select-menu,
        .admin-user-role-select-menu,
        .wiboost-answer-type-menu,
        .wiboost-public-form-select-menu,
        .wiboost-service-dropdown,
        [role="listbox"]
    ) {
        border-color: #ffffff !important;
        background-color: #eef4ff !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #e7efff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), inset 0 -3px 7px rgba(143, 170, 243, .22), 0 18px 36px -12px rgba(43, 58, 103, .38) !important;
    }
    body.wiboost-skeu :is(
        .admin-theme-select-option,
        .admin-category-theme-select-option,
        .admin-product-theme-select-option,
        .admin-promo-theme-select-option,
        .admin-user-role-select-option,
        .wiboost-answer-type-option,
        .wiboost-public-form-select-option,
        .wiboost-service-option,
        [role="option"]
    ):not(.is-selected):not([aria-selected="true"]) {
        border-color: #ffffff !important;
        background-color: #f8fbff !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #edf4ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), inset 0 -2px 4px rgba(143, 170, 243, .20), 0 5px 12px -6px rgba(90, 118, 200, .30) !important;
    }
    body.wiboost-skeu :is(
        .admin-theme-select-option,
        .admin-category-theme-select-option,
        .admin-product-theme-select-option,
        .admin-promo-theme-select-option,
        .admin-user-role-select-option,
        .wiboost-answer-type-option,
        .wiboost-public-form-select-option,
        .wiboost-service-option,
        [role="option"]
    ):is(.is-selected, [aria-selected="true"]) {
        border-color: #ffffff !important;
        color: #ffffff !important;
        background-color: #5a76c8 !important;
        background-image: linear-gradient(180deg, #7b9eed 0%, #5a76c8 52%, #4760a9 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .58), inset 0 -4px 7px rgba(43, 58, 103, .42), 0 8px 18px rgba(43, 58, 103, .30) !important;
    }
    body.wiboost-skeu select:not([multiple]) {
        border-color: #ffffff !important;
        background-color: #eef4ff !important;
        box-shadow: inset 0 2px 5px rgba(143, 170, 243, .26), inset 0 1px 1px rgba(255, 255, 255, .98), 0 7px 15px -9px rgba(90, 118, 200, .34) !important;
    }
    body.wiboost-skeu select option {
        background-color: #ffffff;
        color: #5a76c8;
    }
    body.wiboost-skeu select option:checked {
        background-color: #5a76c8;
        color: #ffffff;
    }

    /* 37) Mutasi saldo: kartu, kepala, area daftar, dan baris memakai satu relief skeu. */
    body.wiboost-skeu [data-wallet-mutation-box] {
        border-color: #ffffff !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #eaf2ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), inset 0 -3px 7px rgba(143, 170, 243, .20), 0 18px 34px -12px rgba(90, 118, 200, .34) !important;
    }
    body.wiboost-skeu [data-wallet-mutation-header] {
        border-style: solid !important;
        border-color: #ffffff !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #e8f0ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), inset 0 -3px 6px rgba(143, 170, 243, .22), 0 8px 16px -12px rgba(90, 118, 200, .38) !important;
    }
    body.wiboost-skeu [data-wallet-mutation-scroll] {
        background-image: linear-gradient(180deg, rgba(241, 246, 255, .88) 0%, rgba(232, 241, 255, .72) 100%) !important;
    }
    body.wiboost-skeu [data-wallet-mutation-scroll] > div {
        border-color: #ffffff !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #edf4ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), inset 0 -2px 5px rgba(143, 170, 243, .20), 0 7px 16px -8px rgba(90, 118, 200, .30) !important;
    }

    /* 38) Sidebar admin benar-benar memakai relief, header, footer, dan item milik sidebar user. */
    body.wiboost-skeu.wiboost-admin-shell .wiboost-admin-sidebar nav a:not([class~="bg-[#5a76c8]"]),
    body.wiboost-skeu.wiboost-admin-shell .wiboost-admin-mobile-sidebar-nav a:not([class~="bg-[#5a76c8]"]) {
        border-color: #ffffff !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #e9f1ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .96), inset 0 -2px 4px rgba(143, 170, 243, .20), 0 5px 12px -6px rgba(90, 118, 200, .28) !important;
    }
    body.wiboost-skeu.wiboost-admin-shell .wiboost-admin-mobile-sidebar-header {
        display: flex !important;
        align-items: center !important;
    }

    /* 39) Mobile: tinggi header sidebar = tinggi header navbar (admin & user). */
    @media (max-width: 1023px) {
        html body.wiboost-skeu { --wiboost-mobile-bar-height: 5.25rem; }
        html body.wiboost-skeu :is(.wiboost-user-navbar-card, .wiboost-admin-navbar-card) {
            box-sizing: border-box !important;
            height: var(--wiboost-mobile-bar-height) !important;
            min-height: var(--wiboost-mobile-bar-height) !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            border-top-width: 0 !important;
        }
        html body.wiboost-skeu :is(.wiboost-mobile-sidebar-header, .wiboost-admin-mobile-sidebar-header) {
            box-sizing: border-box !important;
            display: flex !important;
            align-items: center !important;
            height: var(--wiboost-mobile-bar-height) !important;
            min-height: var(--wiboost-mobile-bar-height) !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }
    }

    /* 40) Tabel: kepala tabel jadi bar skeu FULL-BLEED (menempel penuh ke tepi card, tanpa garis tipis
           sisa), pemisah antar baris jadi garis lembut seragam. Padding atas/kiri/kanan pembungkus
           tabel dinolkan; sel sudah punya padding sendiri sehingga isi tetap lega. */
    body.wiboost-skeu main [class*="table-scroll"]:has(> table),
    body.wiboost-skeu main [class*="table-scroll"]:has(> .inline-block > table),
    body.wiboost-skeu main [class*="table-scroll"] > .inline-block:has(> table) {
        padding-top: 0 !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    body.wiboost-skeu main [class*="table-scroll"]:has(> table),
    body.wiboost-skeu main [class*="table-scroll"]:has(> .inline-block > table) {
        border-radius: inherit;
    }
    html body.wiboost-skeu main table thead :is(th, td)[class],
    html body.wiboost-skeu main table thead :is(th, td) {
        border-top: 0 !important;
        border-bottom: 0 !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #eaf2ff 86%, #d9e5fb 100%) !important;
        box-shadow: none !important;
    }
    html body.wiboost-skeu main table tr[class*="border-b-4"][class*="border-dashed"],
    html body.wiboost-skeu main table tr[class*="border-t-4"][class*="border-dashed"] {
        border-color: transparent !important;
    }
    body.wiboost-skeu [class*="divide-dashed"] > :not([hidden]) ~ :not([hidden]) {
        border-style: solid !important;
        border-color: #e3ebfa !important;
        border-top-width: 2px !important;
        box-shadow: none !important;
    }

    /* 41) Kotak putus-putus utuh (empty state / sumur isian) -> sumur skeu: garis putih solid + cekung. */
    body.wiboost-skeu :is([class~="border-4"], [class~="border-2"])[class*="border-dashed"]:not(input):not(textarea):not(select) {
        border-style: solid !important;
        border-color: #ffffff !important;
        box-shadow:
            inset 0 2px 6px rgba(143, 170, 243, .28),
            inset 0 1px 1px rgba(255, 255, 255, .98),
            0 8px 18px -10px rgba(90, 118, 200, .28) !important;
    }
    body.wiboost-skeu :is([class~="border-4"], [class~="border-2"])[class*="border-dashed"][class~="bg-white"]:not(input):not(textarea):not(select) {
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
    }

    /* 42) Chip/tombol kecil putih (ikon menu, aksi edit/hapus, pagination) -> relief skeu. */
    body.wiboost-skeu :is(span, button, a, div)[class~="bg-white"][class*="rounded-"]:not(.absolute):not(.fixed):not(.admin-detail-card):not(.history-modal-card):not(input):not(textarea):not(select):not([class*="shadow-[#bde0fe]"]):not([class~="border-4"]),
    body.wiboost-skeu :is(.wiboost-pagination-link, .wiboost-pagination-active, .wiboost-pagination-disabled) {
        background-image: linear-gradient(180deg, #ffffff 0%, #e9f1ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), inset 0 -2px 4px rgba(143, 170, 243, .22), 0 4px 10px -4px rgba(90, 118, 200, .28) !important;
    }
    body.wiboost-skeu :is(button, a)[class~="bg-white"][class*="hover:bg-"]:hover {
        background-image: none !important;
    }
    body.wiboost-skeu .wiboost-pagination-active {
        background-image: linear-gradient(180deg, #7b9eed 0%, #5a76c8 52%, #4760a9 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .58), inset 0 -4px 7px rgba(43, 58, 103, .42), 0 8px 18px rgba(43, 58, 103, .28) !important;
    }

    /* 43) Header & footer di dalam kartu yang overflow-visible: ikuti lengkung sudut kartu supaya
           panel full-bleed tidak menonjol keluar dari sudut membulat. */
    body.wiboost-skeu main :is(div, header, section)[class*="border-b-4"][class*="border-dashed"]:first-child {
        border-top-left-radius: inherit !important;
        border-top-right-radius: inherit !important;
    }
    body.wiboost-skeu main :is(div, footer, section)[class*="border-t-4"][class*="border-dashed"]:last-child {
        border-bottom-left-radius: inherit !important;
        border-bottom-right-radius: inherit !important;
    }

    /* 43b) Header/footer di dalam kartu ber-padding: skrip di bawah memberi margin negatif sebesar padding
            kartu (otomatis per breakpoint) sehingga panel menutup PENUH tepi atas/bawah + kiri/kanan. */
    body.wiboost-skeu [data-wiboost-bleed="header"] {
        border-style: solid !important;
        border-color: #ffffff !important;
        align-self: stretch !important;
        width: auto !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .95), inset 0 -3px 6px rgba(163, 187, 251, .20), 0 7px 14px -11px rgba(90, 118, 200, .30) !important;
    }
    body.wiboost-skeu [data-wiboost-bleed="footer"] {
        border-style: solid !important;
        border-color: #ffffff !important;
        align-self: stretch !important;
        width: auto !important;
        background-image: linear-gradient(180deg, #eef4ff 0%, #ffffff 100%) !important;
        box-shadow: inset 0 -1px 0 rgba(255, 255, 255, .95), inset 0 3px 6px rgba(163, 187, 251, .20), 0 -7px 14px -11px rgba(90, 118, 200, .30) !important;
    }

    /* 43a) Header modal detail admin -> PANEL SKEU FULL-BLEED menutup tepi ATAS + kiri/kanan card
            (card p-4 / sm:p-5; sudut di-clip overflow-hidden + rounded milik card). */
    body.wiboost-skeu.wiboost-admin-shell .admin-detail-card > .admin-detail-header {
        align-self: stretch !important;
        width: auto !important;
        border-radius: 0 !important;
        border-bottom: 4px solid #ffffff !important;
        margin: -1rem -1rem 1rem -1rem !important;
        padding: 1rem 1rem 0.9rem 1rem !important;
    }
    @media (min-width: 640px) {
        body.wiboost-skeu.wiboost-admin-shell .admin-detail-card > .admin-detail-header {
            margin: -1.25rem -1.25rem 1rem -1.25rem !important;
            padding: 1.25rem 1.25rem 0.9rem 1.25rem !important;
        }
    }

    body.wiboost-skeu .history-transaction-meta-item {
        background-image: linear-gradient(180deg, #ffffff 0%, #edf4ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), inset 0 -2px 5px rgba(163, 187, 251, .20), 0 8px 18px -10px rgba(90, 118, 200, .30) !important;
    }

    /* 43c) Chip panah dropdown + kartu border-4 putih yang belum punya relief + notis amber transparan. */
    body.wiboost-skeu :is(
        .admin-theme-select-arrow,
        .admin-category-theme-select-arrow,
        .admin-product-theme-select-arrow,
        .admin-promo-theme-select-arrow,
        .admin-user-role-select-arrow,
        .wiboost-answer-type-arrow,
        .wiboost-public-form-select-arrow
    ) {
        background-image: linear-gradient(180deg, #ffffff 0%, #e8f0ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .98), inset 0 -2px 3px rgba(143, 170, 243, .24), 0 3px 8px -3px rgba(90, 118, 200, .30) !important;
    }
    body.wiboost-skeu :is(section, div, article, form)[class~="border-4"][class~="bg-white"][class*="rounded-["]:not([class*="shadow-[#bde0fe]"]):not([class*="border-dashed"]):not(.admin-detail-card):not(.history-modal-card) {
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .95), inset 0 -3px 6px rgba(163, 187, 251, .26), 0 2px 3px rgba(90, 118, 200, .12), 0 14px 28px -10px rgba(90, 118, 200, .30) !important;
    }
    body.wiboost-skeu :is([class~="bg-amber-100/50"], [class~="bg-amber-50/50"], [class~="bg-amber-50/70"]):not(input):not(textarea):not(select) {
        background-image: linear-gradient(180deg, #fffef8 0%, #ffedb4 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .95), inset 0 -2px 4px rgba(217, 155, 22, .22), 0 5px 12px -6px rgba(217, 155, 22, .28) !important;
    }

    /* 45) Kartu/kontainer adalah permukaan statis. Interaksi tetap berada di tombol dan tautan. */
    body.wiboost-skeu main :is(section, article, form, header, footer, div)[class*="rounded-"]:not(.admin-detail-modal):not(.admin-detail-card):not(.history-modal-shell):not(.history-modal-card):not([role="listbox"]):not([x-show]) {
        transition-property: none !important;
    }
    body.wiboost-skeu main :is(section, article, form, header, footer, div)[class*="rounded-"]:not(.admin-detail-modal):not(.admin-detail-card):not(.history-modal-shell):not(.history-modal-card):not([role="listbox"]):not([x-show]):hover,
    body.wiboost-skeu main :is(section, article, form, header, footer, div)[class*="rounded-"]:not(.admin-detail-modal):not(.admin-detail-card):not(.history-modal-shell):not(.history-modal-card):not([role="listbox"]):not([x-show]):active {
        transform: none !important;
        filter: none !important;
    }

    /* 46) Sambungan header dan footer modal riwayat dibuat tipis agar tidak muncul lapisan penghalang. */
    body.wiboost-skeu .history-modal-card [class*="border-b-4"][class*="border-dashed"] {
        border-bottom-width: 2px !important;
        box-shadow: none !important;
        margin-bottom: 0 !important;
    }
    body.wiboost-skeu .history-modal-card [class*="border-t-4"][class*="border-dashed"] {
        border-top-width: 2px !important;
        box-shadow: none !important;
        margin-top: 0 !important;
    }

    /* 48) Detail admin tetap satu kartu; header dan footer menempel tanpa lapisan tambahan. */
    body.wiboost-skeu.wiboost-admin-shell .admin-detail-modal.admin-detail-modal > .admin-detail-card.admin-detail-card {
        overflow: hidden !important;
        border: 4px solid #ffffff !important;
        border-radius: 1.75rem !important;
        background: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        background-color: #ffffff !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        padding: 0 !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.98), inset 0 -3px 7px rgba(163,187,251,.22), 0 22px 54px -20px rgba(15,23,42,.58) !important;
    }
    body.wiboost-skeu.wiboost-admin-shell .admin-detail-modal.admin-detail-modal > .admin-detail-card.admin-detail-card > .admin-detail-header {
        align-self: stretch !important;
        width: auto !important;
        border: 0 !important;
        border-bottom: 2px solid #ffffff !important;
        border-radius: 0 !important;
        background: linear-gradient(180deg, #ffffff 0%, #eaf2ff 100%) !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #eaf2ff 100%) !important;
        margin: 0 !important;
        padding: 1rem 1.1rem !important;
        box-shadow: inset 0 -2px 5px rgba(163,187,251,.16) !important;
    }
    body.wiboost-skeu.wiboost-admin-shell .admin-detail-modal.admin-detail-modal > .admin-detail-card.admin-detail-card > .admin-detail-body {
        border: 0 !important;
        background: transparent !important;
        background-color: transparent !important;
        background-image: none !important;
        box-shadow: none !important;
    }
</style>
<script>
    /* Header/footer berpembatas (border-b-4/border-t-4 putus-putus) di dalam kartu yang punya padding
       dijadikan panel skeu full-bleed: margin negatif = padding kartu, padding dikembalikan ke isi. */
    (() => {
        const HEADER = '[class*="border-b-4"][class*="border-dashed"]';
        const FOOTER = '[class*="border-t-4"][class*="border-dashed"]';
        const SKIP = '.history-modal-card, .admin-detail-card, aside, nav, table, .wiboost-price-zone, .wiboost-order-detail-divider, .wiboost-mobile-sidebar-header, .wiboost-mobile-sidebar-footer, [data-wallet-mutation-header]';
        const PROPS = ['margin-left', 'margin-right', 'margin-top', 'margin-bottom', 'padding-left', 'padding-right', 'padding-top', 'padding-bottom', 'border-top-left-radius', 'border-top-right-radius', 'border-bottom-left-radius', 'border-bottom-right-radius'];
        const px = (value) => parseFloat(value) || 0;
        const IGNORE = 'input[type="hidden"], script, style, template';
        const edge = (parent, header) => {
            const kids = Array.from(parent.children).filter((child) => !child.matches(IGNORE));
            return header ? kids[0] : kids[kids.length - 1];
        };

        const findCard = (el, header) => {
            let node = el;
            let parent = el.parentElement;
            while (parent && parent.tagName !== 'MAIN' && parent !== document.body) {
                if (edge(parent, header) !== node) return null;
                const cs = getComputedStyle(parent);
                if (px(cs.paddingLeft) > 1 || px(cs.paddingRight) > 1) return parent;
                if (px(cs.borderLeftWidth) >= 2 || px(cs.borderTopLeftRadius) >= 16) return null;
                node = parent;
                parent = parent.parentElement;
            }
            return null;
        };

        const reset = (el) => {
            (el._wiboostBleedProps || []).forEach((prop) => el.style.removeProperty(prop));
            el._wiboostBleedProps = [];
            el.removeAttribute('data-wiboost-bleed');
        };

        const apply = (el, header) => {
            reset(el);
            if (el.closest(SKIP)) return;
            const card = findCard(el, header);
            if (!card) return;
            const cc = getComputedStyle(card);
            const own = getComputedStyle(el);
            const pl = px(cc.paddingLeft), pr = px(cc.paddingRight);
            const pEdge = px(header ? cc.paddingTop : cc.paddingBottom);
            const bw = px(cc.borderLeftWidth);
            const radius = Math.max(0, px(cc.borderTopLeftRadius) - bw) + 'px';
            const set = {
                'margin-left': -pl + 'px',
                'margin-right': -pr + 'px',
                'padding-left': px(own.paddingLeft) + pl + 'px',
                'padding-right': px(own.paddingRight) + pr + 'px',
            };
            if (header) {
                set['margin-top'] = -pEdge + 'px';
                set['padding-top'] = px(own.paddingTop) + pEdge + 'px';
                set['border-top-left-radius'] = radius;
                set['border-top-right-radius'] = radius;
            } else {
                set['margin-bottom'] = -pEdge + 'px';
                set['padding-bottom'] = px(own.paddingBottom) + pEdge + 'px';
                set['border-bottom-left-radius'] = radius;
                set['border-bottom-right-radius'] = radius;
            }
            el._wiboostBleedProps = Object.keys(set);
            Object.entries(set).forEach(([prop, value]) => el.style.setProperty(prop, value, 'important'));
            el.setAttribute('data-wiboost-bleed', header ? 'header' : 'footer');
        };

        const run = () => {
            if (!document.body || !document.body.classList.contains('wiboost-skeu')) return;
            document.querySelectorAll('main ' + HEADER).forEach((el) => { if (el === edge(el.parentElement, true)) apply(el, true); });
            document.querySelectorAll('main ' + FOOTER).forEach((el) => { if (el === edge(el.parentElement, false)) apply(el, false); });
        };

        let timer = null;
        const schedule = () => { clearTimeout(timer); timer = setTimeout(run, 80); };

        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run);
        else run();
        window.addEventListener('load', run);
        window.addEventListener('resize', schedule);
        document.addEventListener('alpine:initialized', schedule);
    })();
</script>
