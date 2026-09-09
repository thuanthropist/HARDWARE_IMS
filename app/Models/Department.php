<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'icon',
        'image_path',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function attributeSchemas(): HasMany
    {
        return $this->hasMany(DepartmentAttributeSchema::class)->orderBy('sort_order');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class);
    }

    public function calculatorTypes(): HasMany
    {
        return $this->hasMany(CalculatorType::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Storefront display emoji, keyed by slug — used for cards/badges since
     * no product/department image pipeline exists yet.
     */
    protected function emoji(): Attribute
    {
        return Attribute::make(
            get: fn (): string => match ($this->slug) {
                'building-materials' => '🧱',
                'plumbing' => '🚿',
                'electrical' => '⚡',
                'solar' => '☀️',
                'paints' => '🎨',
                default => '🔧',
            },
        );
    }

    /**
     * CSS custom-property token for this department's accent color.
     */
    protected function colorVar(): Attribute
    {
        return Attribute::make(
            get: fn (): string => match ($this->slug) {
                'building-materials' => '--sf-building',
                'plumbing' => '--sf-plumbing',
                'electrical' => '--sf-electrical',
                'solar' => '--sf-solar',
                'paints' => '--sf-paints',
                default => '--sf-primary',
            },
        );
    }
}
