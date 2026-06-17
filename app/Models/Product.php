<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'sku', 'unit', 'price', 'stock_qty', 'min_stock', 'status'];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_qty' => 'decimal:2',
        'min_stock' => 'decimal:2',
    ];

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        return (float) $this->stock_qty <= (float) $this->min_stock;
    }
}
