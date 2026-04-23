<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Menambahkan 'parent_id', 'description', dan 'emote'
    protected $fillable = ['parent_id', 'name', 'slug', 'description', 'image', 'emote', 'fulfillment_type'];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getBreadcrumbNameAttribute(): string
    {
        $segments = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($segments, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' / ', $segments);
    }

    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }
}
