<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class WiboostEmoji
{
    public static function rootSlug(Category $category): ?string
    {
        $current = $category;

        while ($current->parent) {
            $current = $current->parent;
        }

        return $current->slug;
    }

    public static function shouldUseForSubcategory(Category $category): bool
    {
        $depth = 0;
        $current = $category;

        while ($current->parent) {
            $depth++;
            $current = $current->parent;
        }

        return match ($current->slug) {
            'top-up-game' => $depth >= 2,
            'suntik-sosmed' => $depth >= 2,
            default => false,
        };
    }

    public static function forCategory(Category $category): string
    {
        if (filled($category->emote)) {
            return trim((string) $category->emote);
        }

        return self::forText(implode(' ', array_filter([
            $category->slug,
            $category->name,
            $category->description,
            $category->parent?->slug,
            $category->parent?->name,
        ])));
    }

    public static function forProduct(Product $product): string
    {
        if (filled($product->display_logo_emote)) {
            return trim((string) $product->display_logo_emote);
        }

        return self::forText(implode(' ', array_filter([
            $product->slug,
            $product->name,
            $product->description,
            $product->category?->slug,
            $product->category?->name,
        ])));
    }

    public static function forText(string $text): string
    {
        $text = Str::lower(Str::ascii($text));

        return match (true) {
            Str::contains($text, ['followers', 'follower', 'subscriber', 'subscribers']) => '👥',
            Str::contains($text, ['like', 'likes', 'love']) => '❤️',
            Str::contains($text, ['view', 'views', 'viewer', 'viewers', 'watch']) => '👀',
            Str::contains($text, ['repost', 'retweet', 'regram']) => '🔁',
            Str::contains($text, ['share']) => '📤',
            Str::contains($text, ['save', 'bookmark']) => '🔖',
            Str::contains($text, ['comment', 'komentar']) => '💬',
            Str::contains($text, ['instagram']) => '📸',
            Str::contains($text, ['tiktok']) => '🎵',
            Str::contains($text, ['youtube']) => '▶️',
            Str::contains($text, ['facebook']) => '📘',
            Str::contains($text, ['telegram']) => '✈️',
            Str::contains($text, ['whatsapp']) => '💬',
            Str::contains($text, ['spotify']) => '🎧',
            Str::contains($text, ['netflix']) => '🎬',
            Str::contains($text, ['canva', 'capcut', 'alight', 'editing']) => '🎨',
            Str::contains($text, ['mobile legend', 'mobile-legends', 'mlbb', 'diamond']) => '💎',
            Str::contains($text, ['free fire', 'ff ']) => '🔥',
            Str::contains($text, ['genshin']) => '✨',
            Str::contains($text, ['game', 'top up', 'voucher']) => '🎮',
            Str::contains($text, ['paket', 'data', 'kuota', 'internet']) => '📶',
            Str::contains($text, ['telkomsel', 'axis', 'xl', 'indosat', 'tri', 'smartfren']) => '📱',
            Str::contains($text, ['aplikasi', 'premium', 'app']) => '📲',
            Str::contains($text, ['nomor', 'number']) => '🔢',
            Str::contains($text, ['buzzer']) => '📣',
            Str::contains($text, ['suntik', 'sosmed']) => '🚀',
            Str::contains($text, ['lainnya', 'other']) => '✨',
            default => '✨',
        };
    }
}
