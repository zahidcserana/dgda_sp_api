<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cart extends Model
{
    protected $guarded = [];

    public function AddToCart($data, $user)
    {
        if (!empty($data['token']) && $data['token'] != 'undefined') {
            $cart = $this::where('token', $data['token'])->first();
            if (!empty($cart)) {
                $data['cart_id'] = $cart->id;
            } else {
                return ['success' => false, 'error' => 'Invalid Cart Token!'];
            }
        } else {
            $cartInput = array(
                'pharmacy_id' => $user->pharmacy_id,
                'pharmacy_branch_id' => $user->pharmacy_branch_id,
                'created_by' => $user->id,
            );
            $data['cart_id'] = $this::insertGetId($cartInput);
        }

        $cartItem = new CartItem();

        $addItem = $cartItem->addItem($data);
        if ($addItem) {
            $this->updateCart($data['cart_id']);
            $cartDetails = $this->getCartDetails($data['cart_id']);
            return ['success' => true, 'data' => $cartDetails];
        }
        return ['success' => false, 'error' => 'Medicine not added to cart!'];
    }

    public function updateCart($cartId)
    {
        $cartItem = new CartItem();
        $cartItem = $cartItem
            ->select(DB::raw('SUM(sub_total) as total_sub_total, SUM(quantity) as total_quantity'))
            ->where('cart_id', $cartId)
            ->first();
        $data = array(
            'sub_total' => $cartItem->total_sub_total ?? 0,
            'quantity' => $cartItem->total_quantity,
        );

        $this::where('id', $cartId)->update($data);
        return;
    }

    public function quantityUpdate($data)
    {
        $cart = $this::where('token', $data['token'])->first();
        if ($cart) {
            $cartItemModel = new CartItem();
            if ($cartItemModel->updateQuantity($data)) {
                $this->updateCart($cart->id);
                $cartDetails = $this->getCartDetails($cart->id);
                return ['success' => true, 'data' => $cartDetails];
            } else {
                return ['success' => false, 'error' => 'Something went wrong!'];
            }
        } else {
            return ['success' => false, 'error' => 'Invalid Cart Token!'];
        }
    }

    public function getCartDetails($cartId)
    {
        $cart = $this::find($cartId);

        $cartItems = $cart->items()->get();
        $data = array();
        $data['token'] = $cart->token;
        $data['pharmacy_branch_id'] = $cart->pharmacy_branch_id;
        $data['sub_total'] = $cart->sub_total;
        $data['tax'] = $cart->tax;
        $data['discount'] = $cart->discount;
        $data['remarks'] = $cart->remarks;
        $items = array();
        foreach ($cartItems as $cartItem) {
            $aData = array();
            $aData['id'] = $cartItem->id;
            $aData['medicine_id'] = $cartItem->medicine_id;
            $aData['quantity'] = $cartItem->quantity;
            $aData['batch_no'] = $cartItem->batch_no;
            $aData['dar_no'] = $cartItem->dar_no;
            $aData['unit_price'] = $cartItem->unit_price;
            $aData['sub_total'] = $cartItem->sub_total;
            $aData['discount'] = $cartItem->discount;

            $medicine = $cartItem->medicine;
            $aData['medicine'] = ['id' => $medicine->id, 'brand_name' => $medicine->brand_name];
            $items[] = $aData;
        }
        $data['cart_items'] = $items;

        return $data;
    }

    /**
     * Get all of the item for the cart.
     */
    public function items()
    {
        return $this->hasMany('App\Models\CartItem');
    }


}
