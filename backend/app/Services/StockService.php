<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StockService
{
    public function record(
        int $productId,
        User $user,
        string $type,
        int $quantity,
        ?string $note = null,
        $at = null,
    ): StockMovement {
        if (!in_array($type, ['IN', 'OUT'], true) || $quantity <= 0 || $quantity > 100000000) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantité ou type de mouvement invalide.',
            ]);
        }

        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                // Save stock and history together; an error rolls back both.
                return DB::transaction(function () use ($productId, $user, $type, $quantity, $note, $at) {
                    $delta = $quantity;
                    $query = Product::whereKey($productId)->where('is_active', true);

                    if ($type === 'OUT') {
                        $delta = -$quantity;
                        $query->where('quantity', '>=', $quantity);
                    } else {
                        $query->where('quantity', '<=', 2000000000 - $quantity);
                    }

                    // Write first: SQLite serializes writers. Checking and updating
                    // in one SQL statement prevents concurrent negative stock.
                    $affected = $query->update([
                        'quantity' => DB::raw('quantity + '.(int) $delta),
                        'updated_at' => now(),
                    ]);

                    if ($affected !== 1) {
                        $product = Product::findOrFail($productId);

                        if (!$product->is_active) {
                            $message = 'Ce produit est inactif. Aucun mouvement autorisé.';
                        } elseif ($type === 'OUT') {
                            $message = 'Stock insuffisant : '.$product->quantity.' unité(s) disponible(s).';
                        } else {
                            $message = 'La capacité maximale du stock est atteinte.';
                        }

                        throw ValidationException::withMessages(['quantity' => $message]);
                    }

                    $quantityAfter = (int) Product::whereKey($productId)->value('quantity');

                    return StockMovement::create([
                        'product_id' => $productId,
                        'user_id' => $user->id,
                        'type' => $type,
                        'quantity' => $quantity,
                        'quantity_before' => $quantityAfter - $delta,
                        'quantity_after' => $quantityAfter,
                        'note' => $note,
                        'created_at' => $at ?? now(),
                    ]);
                });
            } catch (QueryException $exception) {
                $sqliteCode = (int) ($exception->errorInfo[1] ?? 0);
                $busy = in_array($sqliteCode, [5, 6], true)
                    || str_contains(strtolower($exception->getMessage()), 'locked');

                if (!$busy) {
                    throw $exception;
                }

                if ($attempt === 2) {
                    throw new HttpException(
                        503,
                        'La base est occupée. Réessayez dans quelques instants.',
                        $exception,
                        ['Retry-After' => '1'],
                    );
                }

                // Wait briefly before retrying a temporary SQLite write lock.
                usleep(100000 * ($attempt + 1));
            }
        }

        throw new \LogicException('Stock movement did not complete.');
    }
}

