<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    public function addItem($data)
    {
        $item = array(
          'medicine_id' => $data['medicine_id'],
          'company_id' => $data['company_id'],
          'quantity' => $data['quantity'],
          'cart_id' => $data['cart_id'],
        );

        $cartItem = CartItem::insertGetId($item);
        if ($cartItem) {
            return true;
        }
        return false;
    }
}
