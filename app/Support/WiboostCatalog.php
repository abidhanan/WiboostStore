<?php

namespace App\Support;

use Illuminate\Support\Str;

class WiboostCatalog
{
    public static function coreCategories(): array
    {
        return [
            [
                'slug' => 'suntik-sosmed',
                'name' => 'Suntik Sosmed',
                'emote' => '🚀',
                'description' => 'Layanan suntik sosmed otomatis via OrderSosmed.',
                'fulfillment_type' => 'auto_api',
            ],
            [
                'slug' => 'top-up-game',
                'name' => 'Top Up Game',
                'emote' => '🎮',
                'description' => 'Top up game otomatis via Digiflazz.',
                'fulfillment_type' => 'auto_api',
            ],
            [
                'slug' => 'kuota-murah',
                'name' => 'Kuota Murah',
                'emote' => '📶',
                'description' => 'Paket data dan kuota operator otomatis via Digiflazz.',
                'fulfillment_type' => 'auto_api',
            ],
            [
                'slug' => 'aplikasi-premium',
                'name' => 'Aplikasi Premium',
                'emote' => '📱',
                'description' => 'Akun aplikasi premium dari gudang kredensial website.',
                'fulfillment_type' => 'stock_based',
            ],
            [
                'slug' => 'nomor-luar',
                'name' => 'Nomor Luar',
                'emote' => '🌐',
                'description' => 'Nomor luar dan OTP manual yang diproses admin.',
                'fulfillment_type' => 'stock_based',
            ],
            [
                'slug' => 'buzzer',
                'name' => 'Buzzer',
                'emote' => '📢',
                'description' => 'Jasa buzzer manual oleh admin dan tim buzzer.',
                'fulfillment_type' => 'manual_action',
            ],
        ];
    }

    public static function coreCategorySlugs(): array
    {
        return collect(self::coreCategories())->pluck('slug')->all();
    }

    public static function categoryOrderSql(string $column = 'slug'): string
    {
        $slugs = self::coreCategorySlugs();

        return 'CASE ' . $column . ' ' . collect($slugs)
            ->map(fn (string $slug, int $index) => "WHEN '{$slug}' THEN {$index}")
            ->implode(' ') . ' ELSE ' . count($slugs) . ' END';
    }

    public static function subcategories(): array
    {
        $platformCategories = self::ordersosmedPlatformCategories();
        $metricDefinitions = self::ordersosmedMetricDefinitions();
        $subcategories = [
            'suntik-sosmed' => array_values($platformCategories),
        ];

        foreach ($platformCategories as $platformSlug => $platformDefinition) {
            $subcategories[$platformSlug] = [];

            foreach ($metricDefinitions as $metricKey => $metricDefinition) {
                $metricSlug = self::ordersosmedMetricSlug($platformSlug, $metricKey);
                $subcategories[$platformSlug][] = self::subcategory(
                    $metricSlug,
                    $metricDefinition['name'],
                    'Layanan ' . Str::lower($metricDefinition['name']) . ' untuk kategori ' . $platformDefinition['name'] . '.',
                    $metricDefinition['logo_text'],
                    $metricDefinition['logo_background'],
                    $metricDefinition['logo_foreground'],
                );

                $subcategories[$metricSlug] = self::ordersosmedRegionalChildren($platformSlug, $metricKey, $metricDefinition);
            }
        }

        return array_merge($subcategories, [
            'top-up-game' => [
                self::subcategory('game-mobile-legends', 'Mobile Legends', 'Produk top up dan voucher Mobile Legends.', 'ML', '#2563EB', '#FFFFFF'),
                self::subcategory('game-free-fire', 'Free Fire', 'Produk diamond dan membership Free Fire.', 'FF', '#F97316', '#FFFFFF'),
                self::subcategory('game-pubg-mobile', 'PUBG Mobile', 'UC dan produk PUBG Mobile.', 'PUBG', '#0F172A', '#FFFFFF'),
                self::subcategory('game-valorant', 'Valorant', 'Valorant Point dan kebutuhan akun Valorant.', 'VAL', '#F43F5E', '#FFFFFF'),
                self::subcategory('game-arena-of-valor', 'Arena of Valor', 'Voucher dan top up Arena of Valor.', 'AOV', '#9333EA', '#FFFFFF'),
                self::subcategory('game-lainnya', 'Lainnya', 'Game lain yang tersedia di provider Digiflazz.', 'GAME', '#5A76C8', '#FFFFFF'),
            ],
            'kuota-murah' => [
                self::subcategory('kuota-axis', 'Axis', 'Paket data dan voucher Axis.', 'AX', '#7C3AED', '#FFFFFF'),
                self::subcategory('kuota-telkomsel', 'Telkomsel', 'Kuota dan paket Telkomsel / by.U.', 'TS', '#DC2626', '#FFFFFF'),
                self::subcategory('kuota-tri', 'Tri', 'Paket data dan voucher Tri.', 'TRI', '#111827', '#FFFFFF'),
                self::subcategory('kuota-indosat', 'Indosat', 'Paket internet Indosat / IM3.', 'IM3', '#F59E0B', '#111827'),
                self::subcategory('kuota-smartfren', 'Smartfren', 'Paket data Smartfren.', 'SF', '#EC4899', '#FFFFFF'),
                self::subcategory('kuota-xl', 'XL Axiata', 'Paket data XL dan voucher XL.', 'XL', '#1D4ED8', '#FFFFFF'),
                self::subcategory('kuota-lainnya', 'Lainnya', 'Operator lain yang tidak masuk subkategori utama.', 'NET', '#5A76C8', '#FFFFFF'),
            ],
            'aplikasi-premium' => [
                self::subcategory('premium-netflix', 'Netflix', 'Akun Netflix sharing atau private sesuai stok yang kamu masukkan.', 'N', '#E50914', '#FFFFFF'),
                self::subcategory('premium-spotify', 'Spotify', 'Akun Spotify premium family atau individual.', 'SP', '#1DB954', '#FFFFFF'),
                self::subcategory('premium-canva', 'Canva', 'Akun Canva Pro dan akses desain premium.', 'C', '#06B6D4', '#FFFFFF'),
                self::subcategory('premium-youtube', 'YouTube Premium', 'Akun YouTube Premium / Music Premium.', 'YT', '#FF0033', '#FFFFFF'),
                self::subcategory('premium-lainnya', 'Lainnya', 'Aplikasi premium lain yang dikelola manual.', 'APP', '#5A76C8', '#FFFFFF'),
            ],
            'nomor-luar' => [
                self::subcategory('nomor-whatsapp', 'WhatsApp', 'Nomor luar untuk kebutuhan WhatsApp atau verifikasi terkait.', 'WA', '#25D366', '#FFFFFF'),
                self::subcategory('nomor-telegram', 'Telegram', 'Nomor luar untuk Telegram dan layanan sejenis.', 'TG', '#229ED9', '#FFFFFF'),
                self::subcategory('nomor-all-app', 'All App / OTP', 'Nomor luar untuk berbagai aplikasi dan kebutuhan OTP.', 'OTP', '#F59E0B', '#111827'),
                self::subcategory('nomor-lainnya', 'Lainnya', 'Nomor luar custom yang diproses manual oleh admin.', 'NUM', '#5A76C8', '#FFFFFF'),
            ],
            'buzzer' => [
                self::subcategory('buzzer-instagram', 'Instagram', 'Campaign buzzer manual untuk Instagram.', 'IG', '#E4405F', '#FFFFFF'),
                self::subcategory('buzzer-tiktok', 'TikTok', 'Campaign buzzer manual untuk TikTok.', 'TT', '#111827', '#FFFFFF'),
                self::subcategory('buzzer-youtube', 'YouTube', 'Campaign buzzer manual untuk YouTube.', 'YT', '#FF0033', '#FFFFFF'),
                self::subcategory('buzzer-twitter-x', 'X / Twitter', 'Campaign buzzer manual untuk X / Twitter.', 'X', '#111827', '#FFFFFF'),
                self::subcategory('buzzer-lainnya', 'Lainnya', 'Campaign buzzer manual untuk platform lain.', 'BZ', '#5A76C8', '#FFFFFF'),
            ],
        ]);
    }

    public static function deprecatedCategorySlugs(): array
    {
        $oldMetricSlugs = [];

        foreach (array_keys(self::ordersosmedMetricDefinitions()) as $metricKey) {
            $rootSlug = 'sosmed-' . $metricKey;
            $oldMetricSlugs[] = $rootSlug;

            foreach (['indonesia', 'luar-negeri', 'global'] as $region) {
                $oldMetricSlugs[] = $rootSlug . '-' . $region;
            }
        }

        return $oldMetricSlugs;
    }

    public static function subcategoriesFor(string $parentSlug): array
    {
        return self::subcategories()[$parentSlug] ?? [];
    }

    public static function subcategoryBySlug(string $slug): ?array
    {
        return collect(self::subcategories())
            ->flatten(1)
            ->firstWhere('slug', $slug);
    }

    public static function targetMetaForTopCategory(?string $topCategorySlug): ?array
    {
        $fields = self::checkoutFieldsForTopCategory($topCategorySlug);

        if ($fields === []) {
            return null;
        }

        $primaryField = collect($fields)->firstWhere('target_summary', true) ?? $fields[0];

        return [
            'label' => $primaryField['label'],
            'placeholder' => $primaryField['placeholder'],
            'hint' => $primaryField['hint'],
        ];
    }

    public static function checkoutFieldsForTopCategory(?string $topCategorySlug): array
    {
        return match ($topCategorySlug) {
            'suntik-sosmed' => [
                self::checkoutField(
                    'target_data',
                    'Username akun / link postingan',
                    'text',
                    'Contoh: @username atau https://instagram.com/p/abc123',
                    'Masukkan username akun atau link postingan yang aktif sesuai layanan suntik sosmed yang dipilih.',
                    ['required', 'string', 'min:3', 'max:255'],
                    targetSummary: true
                ),
            ],
            'top-up-game' => [
                self::checkoutField(
                    'game_user_id',
                    'User ID game',
                    'text',
                    'Contoh: 123456789',
                    'Masukkan User ID game dengan benar.',
                    ['required', 'string', 'min:2', 'max:100']
                ),
                self::checkoutField(
                    'game_zone_id',
                    'Zone ID / Server ID',
                    'text',
                    'Contoh: 1234',
                    'Masukkan Zone ID atau Server ID yang tertera di akun game.',
                    ['required', 'string', 'min:1', 'max:50']
                ),
            ],
            'kuota-murah' => [
                self::checkoutField(
                    'phone_number',
                    'Nomor handphone',
                    'tel',
                    'Contoh: 081234567890',
                    'Masukkan nomor handphone aktif yang akan menerima kuota atau paket data.',
                    ['required', 'string', 'min:8', 'max:20'],
                    targetSummary: true
                ),
            ],
            'aplikasi-premium' => [
                self::checkoutField(
                    'app_email',
                    'Email khusus aplikasi',
                    'email',
                    'Contoh: emailkhusus@gmail.com',
                    'Masukkan email khusus aplikasi yang akan dipakai untuk akun premium ini.',
                    ['required', 'string', 'email', 'max:255'],
                    targetSummary: true
                ),
            ],
            'nomor-luar' => [],
            'buzzer' => [
                self::checkoutField(
                    'campaign_link',
                    'Link postingan / link Google Maps',
                    'url',
                    'Contoh: https://instagram.com/p/abc123 atau https://maps.app.goo.gl/xxxx',
                    'Masukkan link postingan atau link Google Maps yang akan dipakai tim buzzer.',
                    ['required', 'string', 'url', 'max:500'],
                    targetSummary: true
                ),
                self::checkoutField(
                    'comment_brief',
                    'Deskripsi komentar buzzer',
                    'textarea',
                    'Contoh: Komentar bernada positif, natural, dan menonjolkan pelayanan cepat.',
                    'Tuliskan arahan komentar yang kamu inginkan agar admin dan tim buzzer bisa mengeksekusi campaign dengan tepat.',
                    ['required', 'string', 'min:5', 'max:2000']
                ),
            ],
            default => [],
        };
    }

    public static function resolveDigiflazzTopCategorySlug(string $name, string $brand = '', string $category = '', string $description = ''): ?string
    {
        $text = Str::lower(trim("{$brand} {$category} {$name} {$description}"));
        $telcoBrands = ['axis', 'telkomsel', 'by.u', 'tri', 'indosat', 'smartfren', 'xl'];
        $quotaKeywords = ['data', 'kuota', 'internet', 'paket', 'voucher', 'gb', 'mb', 'unlimited', 'combo', 'freedom', 'flash', 'happy', 'hotrod', 'xtra'];
        $quotaExclusions = ['pulsa', 'masa aktif', 'sms', 'telpon', 'voice'];
        $gameKeywords = [
            'games',
            'game',
            'diamond',
            'diamonds',
            'weekly diamond pass',
            'uc',
            'cp',
            'valorant',
            'vp',
            'steam',
            'point blank',
            'pb cash',
            'arena of valor',
            'genshin',
            'codm',
            'call of duty',
            'free fire',
            'mobile legends',
            'mobilelegend',
            'pubg',
        ];

        $mentionsTelcoBrand = collect($telcoBrands)->contains(fn (string $keyword) => str_contains($text, $keyword));
        $mentionsQuotaPackage = collect($quotaKeywords)->contains(fn (string $keyword) => str_contains($text, $keyword));
        $mentionsQuotaExclusion = collect($quotaExclusions)->contains(fn (string $keyword) => str_contains($text, $keyword));

        if ($mentionsTelcoBrand && $mentionsQuotaPackage && ! $mentionsQuotaExclusion) {
            return 'kuota-murah';
        }

        foreach ($gameKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return 'top-up-game';
            }
        }

        return null;
    }

    public static function resolveDigiflazzSubcategorySlug(string $topCategorySlug, string $name, string $brand = '', string $description = ''): ?string
    {
        $text = Str::lower(trim("{$brand} {$name} {$description}"));

        return match ($topCategorySlug) {
            'top-up-game' => match (true) {
                str_contains($text, 'mobile legends'), str_contains($text, 'mobilelegend') => 'game-mobile-legends',
                str_contains($text, 'free fire'), str_contains($text, 'freefire') => 'game-free-fire',
                str_contains($text, 'pubg') => 'game-pubg-mobile',
                str_contains($text, 'valorant'), str_contains($text, 'vp') => 'game-valorant',
                str_contains($text, 'arena of valor'), str_contains($text, 'aov') => 'game-arena-of-valor',
                default => 'game-lainnya',
            },
            'kuota-murah' => match (true) {
                str_contains($text, 'axis') => 'kuota-axis',
                str_contains($text, 'telkomsel'), str_contains($text, 'by.u') => 'kuota-telkomsel',
                str_contains($text, ' tri '), str_starts_with($text, 'tri'), str_contains($text, '3 ') => 'kuota-tri',
                str_contains($text, 'indosat'), str_contains($text, 'im3') => 'kuota-indosat',
                str_contains($text, 'smartfren') => 'kuota-smartfren',
                str_contains($text, ' xl '), str_starts_with($text, 'xl'), str_contains($text, 'xtra') => 'kuota-xl',
                default => 'kuota-lainnya',
            },
            default => null,
        };
    }

    public static function resolveOrdersosmedSubcategorySlug(string $name, string $providerCategory = '', string $description = ''): string
    {
        $text = Str::lower(trim("{$providerCategory} {$name} {$description}"));
        $platformSlug = self::resolveOrdersosmedPlatformSlug($text);
        $metricKey = self::resolveOrdersosmedMetricKey($text);
        $regionSuffix = self::resolveOrdersosmedRegionSuffix($text);

        return self::ordersosmedMetricSlug($platformSlug, $metricKey) . '-' . $regionSuffix;
    }

    public static function manualProductTemplates(): array
    {
        return [
            self::template('premium-netflix', 'Netflix Premium Sharing', 'account', 'Template akun Netflix sharing. Isi email, password, profil, PIN, link akses, tutorial, dan kuota pemakaian di gudang kredensial.'),
            self::template('premium-spotify', 'Spotify Premium Family', 'account', 'Template akun Spotify premium. Isi data akun, profil, tutorial, dan batas pemakaian sesuai stok yang kamu punya.'),
            self::template('premium-canva', 'Canva Pro Sharing', 'account', 'Template akun Canva Pro sharing. Cocok untuk stok akun yang bisa dipakai beberapa pembeli.'),
            self::template('premium-youtube', 'YouTube Premium Family', 'account', 'Template akun YouTube Premium / Music Premium. Isi stok kredensial dari panel gudang akun.'),
            self::template('premium-lainnya', 'Aplikasi Premium Custom', 'account', 'Template akun premium custom untuk aplikasi selain Netflix, Spotify, Canva, dan YouTube Premium.'),
            self::template('nomor-whatsapp', 'Nomor Luar WhatsApp', 'number', 'Template nomor luar untuk WhatsApp. Nomor dan tutorial bisa dikirim otomatis, OTP dibantu admin bila diperlukan.'),
            self::template('nomor-telegram', 'Nomor Luar Telegram', 'number', 'Template nomor luar untuk Telegram. Cocok untuk stok nomor yang dikelola dari gudang nomor.'),
            self::template('nomor-all-app', 'Nomor OTP All App', 'number', 'Template nomor luar untuk kebutuhan OTP berbagai aplikasi.'),
            self::template('nomor-lainnya', 'Nomor Luar Custom', 'number', 'Template nomor luar custom untuk kebutuhan selain WhatsApp dan Telegram.'),
            self::template('buzzer-instagram', 'Campaign Buzzer Instagram', 'manual', 'Template jasa buzzer Instagram yang dikerjakan manual oleh admin dan tim buzzer.'),
            self::template('buzzer-tiktok', 'Campaign Buzzer TikTok', 'manual', 'Template jasa buzzer TikTok yang dikerjakan manual oleh admin dan tim buzzer.'),
            self::template('buzzer-youtube', 'Campaign Buzzer YouTube', 'manual', 'Template jasa buzzer YouTube yang dikerjakan manual oleh admin dan tim buzzer.'),
            self::template('buzzer-twitter-x', 'Campaign Buzzer X / Twitter', 'manual', 'Template jasa buzzer X / Twitter yang dikerjakan manual oleh admin dan tim buzzer.'),
            self::template('buzzer-lainnya', 'Campaign Buzzer Custom', 'manual', 'Template jasa buzzer custom untuk campaign di platform lain.'),
        ];
    }

    protected static function ordersosmedPlatformCategories(): array
    {
        return [
            'sosmed-instagram' => self::subcategory('sosmed-instagram', 'Instagram', 'Layanan Instagram seperti like, followers, views, komentar, dan lainnya.', 'IG', '#E4405F', '#FFFFFF'),
            'sosmed-tiktok' => self::subcategory('sosmed-tiktok', 'TikTok', 'Layanan TikTok seperti viewers, likes, followers, share, dan lainnya.', 'TT', '#111827', '#FFFFFF'),
            'sosmed-youtube' => self::subcategory('sosmed-youtube', 'YouTube', 'Layanan YouTube seperti subscriber, views, likes, watchtime, dan lainnya.', 'YT', '#FF0033', '#FFFFFF'),
            'sosmed-telegram' => self::subcategory('sosmed-telegram', 'Telegram', 'Layanan Telegram seperti member, views, reactions, dan lainnya.', 'TG', '#229ED9', '#FFFFFF'),
            'sosmed-twitter-x' => self::subcategory('sosmed-twitter-x', 'X / Twitter', 'Layanan X / Twitter seperti followers, likes, retweet, views, dan lainnya.', 'X', '#111827', '#FFFFFF'),
            'sosmed-facebook' => self::subcategory('sosmed-facebook', 'Facebook', 'Layanan Facebook seperti page like, followers, comments, dan lainnya.', 'FB', '#1877F2', '#FFFFFF'),
            'sosmed-website-traffic' => self::subcategory('sosmed-website-traffic', 'Website & Traffic', 'Layanan traffic website, visit, SEO, dan kebutuhan growth lainnya.', 'WEB', '#0F766E', '#FFFFFF'),
            'sosmed-platform-lainnya' => self::subcategory('sosmed-platform-lainnya', 'Lainnya', 'Platform sosial media lain yang belum masuk daftar utama.', 'ETC', '#5A76C8', '#FFFFFF'),
        ];
    }

    protected static function ordersosmedMetricDefinitions(): array
    {
        return [
            'like' => ['name' => 'Like', 'logo_text' => 'LIKE', 'logo_background' => '#F43F5E', 'logo_foreground' => '#FFFFFF'],
            'followers' => ['name' => 'Followers', 'logo_text' => 'FOL', 'logo_background' => '#2563EB', 'logo_foreground' => '#FFFFFF'],
            'views' => ['name' => 'Views / Viewers', 'logo_text' => 'VIEW', 'logo_background' => '#0F766E', 'logo_foreground' => '#FFFFFF'],
            'comments' => ['name' => 'Komentar', 'logo_text' => 'COM', 'logo_background' => '#F59E0B', 'logo_foreground' => '#111827'],
            'subscribers' => ['name' => 'Subscribers', 'logo_text' => 'SUB', 'logo_background' => '#DC2626', 'logo_foreground' => '#FFFFFF'],
            'share-save' => ['name' => 'Share & Save', 'logo_text' => 'SHR', 'logo_background' => '#7C3AED', 'logo_foreground' => '#FFFFFF'],
            'members' => ['name' => 'Members & Join', 'logo_text' => 'MEM', 'logo_background' => '#0891B2', 'logo_foreground' => '#FFFFFF'],
            'rating-review' => ['name' => 'Rating & Review', 'logo_text' => 'REV', 'logo_background' => '#16A34A', 'logo_foreground' => '#FFFFFF'],
            'traffic' => ['name' => 'Traffic & Visit', 'logo_text' => 'TRF', 'logo_background' => '#1D4ED8', 'logo_foreground' => '#FFFFFF'],
            'lainnya' => ['name' => 'Lainnya', 'logo_text' => 'ETC', 'logo_background' => '#5A76C8', 'logo_foreground' => '#FFFFFF'],
        ];
    }

    protected static function ordersosmedMetricSlug(string $platformSlug, string $metricKey): string
    {
        return $platformSlug . '-' . $metricKey;
    }

    protected static function ordersosmedRegionalChildren(string $platformSlug, string $metricKey, array $metricDefinition): array
    {
        $metricSlug = self::ordersosmedMetricSlug($platformSlug, $metricKey);

        return [
            self::subcategory(
                $metricSlug . '-indonesia',
                $metricDefinition['name'] . ' Indonesia',
                'Layanan ' . Str::lower($metricDefinition['name']) . ' dengan target akun atau audiens Indonesia.',
                'ID',
                $metricDefinition['logo_background'],
                $metricDefinition['logo_foreground'],
            ),
            self::subcategory(
                $metricSlug . '-luar-negeri',
                $metricDefinition['name'] . ' Luar Negeri',
                'Layanan ' . Str::lower($metricDefinition['name']) . ' dengan target non-Indonesia atau negara tertentu.',
                'INT',
                $metricDefinition['logo_background'],
                $metricDefinition['logo_foreground'],
            ),
            self::subcategory(
                $metricSlug . '-global',
                $metricDefinition['name'] . ' Global',
                'Layanan ' . Str::lower($metricDefinition['name']) . ' campuran, worldwide, atau belum punya label negara spesifik.',
                'GLB',
                $metricDefinition['logo_background'],
                $metricDefinition['logo_foreground'],
            ),
        ];
    }

    protected static function resolveOrdersosmedPlatformSlug(string $text): string
    {
        return match (true) {
            str_contains($text, 'instagram'), str_contains($text, 'ig ') => 'sosmed-instagram',
            str_contains($text, 'tiktok') => 'sosmed-tiktok',
            str_contains($text, 'youtube'), str_contains($text, 'yt ') => 'sosmed-youtube',
            str_contains($text, 'telegram') => 'sosmed-telegram',
            str_contains($text, 'twitter'), str_contains($text, 'tweet'), str_contains($text, 'x / twitter') => 'sosmed-twitter-x',
            str_contains($text, 'facebook'), str_contains($text, 'fb ') => 'sosmed-facebook',
            str_contains($text, 'traffic'), str_contains($text, 'visit'), str_contains($text, 'seo'), str_contains($text, 'website') => 'sosmed-website-traffic',
            default => 'sosmed-platform-lainnya',
        };
    }

    protected static function resolveOrdersosmedMetricKey(string $text): string
    {
        return match (true) {
            str_contains($text, 'rating'), str_contains($text, 'review'), str_contains($text, 'ulasan'), str_contains($text, 'google maps'), str_contains($text, 'maps') => 'rating-review',
            str_contains($text, 'subscriber'), str_contains($text, 'subs') => 'subscribers',
            str_contains($text, 'comment'), str_contains($text, 'komen'), str_contains($text, 'komentar') => 'comments',
            str_contains($text, 'follower') => 'followers',
            str_contains($text, 'like'), str_contains($text, 'favorite'), str_contains($text, 'favourite'), str_contains($text, 'heart') => 'like',
            str_contains($text, 'share'), str_contains($text, 'retweet'), str_contains($text, 'repost'), str_contains($text, 'save'), str_contains($text, 'bookmark') => 'share-save',
            str_contains($text, 'member'), str_contains($text, 'join group'), str_contains($text, 'join channel'), str_contains($text, 'channel member') => 'members',
            str_contains($text, 'traffic'), str_contains($text, 'visit'), str_contains($text, 'seo'), str_contains($text, 'click'), str_contains($text, 'klik') => 'traffic',
            str_contains($text, 'view'), str_contains($text, 'viewer'), str_contains($text, 'reach'), str_contains($text, 'impression'), str_contains($text, 'play'), str_contains($text, 'watchtime') => 'views',
            default => 'lainnya',
        };
    }

    protected static function resolveOrdersosmedRegionSuffix(string $text): string
    {
        $indonesiaKeywords = ['indonesia', 'indo', 'lokal', 'indonesian', 'wni'];
        $foreignKeywords = [
            'luar negeri',
            'non indo',
            'international',
            'foreign',
            'usa',
            'turkey',
            'india',
            'brazil',
            'japan',
            'korea',
            'russia',
            'arab',
            'thailand',
            'vietnam',
            'philippines',
            'mexico',
            'malaysia',
            'singapore',
            'europe',
        ];
        $globalKeywords = ['global', 'worldwide', 'campuran', 'mixed', 'all country', 'all negara'];

        if (collect($indonesiaKeywords)->contains(fn (string $keyword) => str_contains($text, $keyword))) {
            return 'indonesia';
        }

        if (collect($foreignKeywords)->contains(fn (string $keyword) => str_contains($text, $keyword))) {
            return 'luar-negeri';
        }

        if (collect($globalKeywords)->contains(fn (string $keyword) => str_contains($text, $keyword))) {
            return 'global';
        }

        return 'global';
    }

    protected static function subcategory(
        string $slug,
        string $name,
        string $description,
        string $logoText,
        string $logoBackground,
        string $logoForeground
    ): array {
        return [
            'slug' => $slug,
            'name' => $name,
            'description' => $description,
            'logo_text' => $logoText,
            'logo_background' => $logoBackground,
            'logo_foreground' => $logoForeground,
        ];
    }

    protected static function checkoutField(
        string $name,
        string $label,
        string $type,
        string $placeholder,
        string $hint,
        array $rules,
        bool $targetSummary = false
    ): array {
        return [
            'name' => $name,
            'label' => $label,
            'type' => $type,
            'placeholder' => $placeholder,
            'hint' => $hint,
            'rules' => $rules,
            'target_summary' => $targetSummary,
        ];
    }

    protected static function template(string $subcategorySlug, string $name, string $processType, string $description): array
    {
        $topCategorySlug = Str::before($subcategorySlug, '-');

        return [
            'subcategory_slug' => $subcategorySlug,
            'top_category_slug' => match ($topCategorySlug) {
                'premium' => 'aplikasi-premium',
                'nomor' => 'nomor-luar',
                'buzzer' => 'buzzer',
                default => $topCategorySlug,
            },
            'name' => $name,
            'process_type' => $processType,
            'description' => $description,
        ];
    }
}
