<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class StockMovement extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'product_id', 'user_id', 'type', 'quantity',
        'quantity_before', 'quantity_after', 'note', 'created_at',
    ];

    protected static function booted(): void
    {
        // History is append-only; corrections require a new movement.
        static::updating(function () {
            throw new LogicException('Historique immuable.');
        });

        static::deleting(function () {
            throw new LogicException('Historique immuable.');
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

