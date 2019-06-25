<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public function AddToCart($data)
    {
        if (!empty($data['token'])) {
            $cart = $this::where('token', $data['token'])->first();
            if (!empty($cart)) {
                $data['cart_id'] = $cart->id;
            }
            return false;
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
        return ['success' => false];
    }

    public function getCartDetails($cartId)
    {
        $cart = $this::where('id', $cartId)->first();

        return $cart;
    }
}
