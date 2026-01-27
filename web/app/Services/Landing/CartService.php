<?php

namespace App\Services\Landing;

use App\Exceptions\NotFoundException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Get cart with items
     *
     * @param  Request  $request
     * @return array
     */
    public function getCart(Request $request): array
    {
        $cart = $this->resolveCart($request);
        $cart->load('items.product');

        $items = $cart->items->map(function (CartItem $item) {
            return [
                'id' => $item->id,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'thumbnail' => $item->product->thumbnail,
                ],
                'qty' => $item->qty,
                'price' => $item->price,
                'total' => $item->qty * $item->price,
            ];
        });

        return [
            'items' => $items,
            'total' => $items->sum('total'),
        ];
    }

    /**
     * Add item to cart
     *
     * @param  Request  $request
     * @param  array  $data
     * @return CartItem
     * @throws NotFoundException
     */
    public function addItem(Request $request, array $data): CartItem
    {
        $cart = $this->resolveCart($request);

        $product = $this->productRepository->findById($data['product_id']);

        if (!$product) {
            throw new NotFoundException("Product with ID {$data['product_id']} not found");
        }

        $variantId = $data['variant_id'] ?? null;

        $item = $cart->items()->firstOrCreate([
            'product_id' => $product->id,
            'product_variant_id' => $variantId,
        ], [
            'qty' => 0,
            'price' => $product->price,
        ]);

        $item->qty += $data['qty'];
        $item->save();

        return $item;
    }

    /**
     * Update cart item quantity
     *
     * @param  CartItem  $item
     * @param  int  $qty
     * @return CartItem
     */
    public function updateItem(CartItem $item, int $qty): CartItem
    {
        $item->update(['qty' => $qty]);
        return $item->fresh();
    }

    /**
     * Remove item from cart
     *
     * @param  CartItem  $item
     * @return bool
     */
    public function removeItem(CartItem $item): bool
    {
        return $item->delete();
    }

    /**
     * Clear all items from cart
     *
     * @param  Request  $request
     * @return bool
     */
    public function clearCart(Request $request): bool
    {
        $cart = $this->resolveCart($request);
        return $cart->items()->delete() > 0;
    }

    /**
     * Resolve cart from request
     *
     * @param  Request  $request
     * @return Cart
     */
    public function resolveCart(Request $request): Cart
    {
        $user = Auth::user();

        if ($user) {
            return $this->cartRepository->findOrCreateByUserId($user->id);
        }

        $sessionId = $request->session()->getId();
        return $this->cartRepository->findOrCreateBySessionId($sessionId);
    }
}

