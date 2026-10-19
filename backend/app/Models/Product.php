<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    // Quantity is excluded: only StockService changes it.
    protected $fillable = ['category_id', 'sku', 'name', 'brand', 'unit_price', 'min_stock', 'is_active', 'image_url'];

    protected $appends = ['stock_status'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
            'unit_price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->quantity === 0) {
            return 'out';
        }

        if ($this->quantity <= $this->min_stock) {
            return 'low';
        }

        return 'healthy';
    }
}

