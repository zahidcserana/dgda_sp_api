<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public function AddToCart($data)
    {
        if (!empty($data['token']) && $data['token'] != 'undefined') {
            $cart = $this::where('token', $data['token'])->first();
            if (!empty($cart)) {
                $data['cart_id'] = $cart->id;
            } else {
                return ['success' => false, 'error' => 'Invalid Cart Token!'];
            }
        } else {
            $cartInput = array();
            $data['cart_id'] = $this::insertGetId($cartInput);
        }

        $cartItem = new CartItem();

        $addItem = $cartItem->addItem($data);
        if ($addItem) {
            $cartDetails = $this->getCartDetails($data['cart_id']);
            return ['success' => true, 'data' => $cartDetails];
        }
        return ['success' => false, 'error' => 'Medicine not added to cart!'];
    }

    public function getCartDetails($cartId)
    {
        $cart = $this::find($cartId);

        $cartDetails = $cart->items()->get();

        $cart['cart_items'] = $cartDetails;

        return $cart;
    }

    /**
     * Get all of the item for the cart.
     */
    public function items()
    {
        return $this->hasMany('App\Models\CartItem');
    }
}
