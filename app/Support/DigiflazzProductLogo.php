<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Str;

class DigiflazzProductLogo
{
    public const DIAMOND = '💎';
    public const VOUCHER = '🎫';
    public const USERNAME_CHECK = '📡';

    public static function emoteForDigiflazzItem(array $item, ?string $topCategorySlug): ?string
    {
        if ($topCategorySlug !== 'top-up-game') {
            return null;
        }

        return self::emoteForProductText(implode(' ', array_filter([
            $item['brand'] ?? null,
            $item['category'] ?? null,
            $item['product_name'] ?? null,
            $item['buyer_sku_code'] ?? null,
            $item['desc'] ?? null,
        ])));
    }

    public static function emoteForProduct(Product $product): string
    {
        $product->loadMissing('category.parent.parent.parent');

        return self::emoteForProductText(implode(' ', array_filter([
            $product->category?->breadcrumb_name,
            $product->provider_product_id,
            $product->name,
            $product->description,
        ])));
    }

    public static function emoteForProductText(string $text): string
    {
        $text = self::normalize($text);

        if (self::isUsernameCheckText($text)) {
            return self::USERNAME_CHECK;
        }

        if (self::isDiamondProductText($text)) {
            return self::DIAMOND;
        }

        return self::VOUCHER;
    }

    protected static function isUsernameCheckText(string $text): bool
    {
        return self::containsAny($text, [
            ' cek username ',
            ' check username ',
            ' cek user ',
            ' check user ',
            ' cek nickname ',
            ' check nickname ',
            ' cek nick ',
            ' check nick ',
            ' cek id ',
            ' check id ',
            ' cek akun ',
            ' check account ',
            ' validasi id ',
            ' validasi akun ',
            ' inquiry ',
        ]);
    }

    protected static function isDiamondProductText(string $text): bool
    {
        if (! self::containsAny($text, [' diamond ', ' diamonds '])) {
            return false;
        }

        return ! self::containsAny($text, [
            ' diamond pass ',
            ' weekly diamond pass ',
            ' pass ',
            ' membership ',
            ' member ',
            ' mingguan ',
            ' bulanan ',
            ' weekly ',
            ' monthly ',
            ' voucher ',
            ' token ',
            ' tiket ',
            ' ticket ',
        ]);
    }

    protected static function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected static function normalize(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = Str::lower($text);
        $text = preg_replace('/[^\pL\pN]+/u', ' ', $text) ?? $text;

        return ' ' . trim(preg_replace('/\s+/u', ' ', $text) ?? $text) . ' ';
    }
}
