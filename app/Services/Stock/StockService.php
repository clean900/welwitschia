<?php

namespace App\Services\Stock;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\AuditLog;
use App\Services\Automation\WebhookDispatcher;

/**
 * Movimentos de stock (entradas/saídas) com registo de histórico.
 */
class StockService
{
    public function move(Product $product, string $type, float $quantity, ?string $note = null): StockMovement
    {
        $delta = $type === 'saida' ? -abs($quantity) : abs($quantity);
        $product->stock_qty = round((float) $product->stock_qty + $delta, 2);
        $product->save();

        $movement = $product->movements()->create([
            'type' => $type,
            'quantity' => abs($quantity),
            'balance_after' => $product->stock_qty,
            'note' => $note,
        ]);

        AuditLog::record('stock.' . $type, [
            'product' => $product->name,
            'quantity' => abs($quantity),
            'balance' => (float) $product->stock_qty,
        ], Product::class, $product->id);

        if ($product->isLowStock()) {
            WebhookDispatcher::send('stock.low', [
                'product' => $product->name,
                'stock' => (float) $product->stock_qty,
                'min' => (float) $product->min_stock,
            ]);
        }

        return $movement;
    }
}
