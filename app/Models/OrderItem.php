<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\This;

class OrderItem extends Model
{
    protected $fillable = [
        'medicine_id', 'company_id', 'quantity', 'order_id', 'exp_date', 'mfg_date', 'batch_no', 'dar_no', 'unit_price',
        'sub_total', 'discount'
    ];

    public function addItem($orderId, $cartId)
    {
        $cartItemModel = new CartItem();
        $cartItems = $cartItemModel->where('cart_id', $cartId)->get();
        foreach ($cartItems as $cartItem) {
            $itemInput = array(
                'medicine_id' => $cartItem->medicine_id,
                'company_id' => $cartItem->company_id,
                'quantity' => $cartItem->quantity,
                'order_id' => $orderId,
                'exp_date' => $cartItem->exp_date,
                'mfg_date' => $cartItem->mfg_date,
                'batch_no' => $cartItem->batch_no,
                'dar_no' => $cartItem->dar_no,
                'unit_price' => $cartItem->unit_price,
                'sub_total' => $cartItem->sub_total,
                'discount' => $cartItem->discount,
            );
            $this::create($itemInput);
        }
        return;
    }

    public function medicine()
    {
        return $this->belongsTo('App\Models\Medicine');
    }
}
