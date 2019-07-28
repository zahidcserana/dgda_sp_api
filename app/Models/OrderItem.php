<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\This;

class OrderItem extends Model
{
    protected $guarded = [];

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

    public function deleteItem($data)
    {
        $orderModel = new Order();
        $item = $this::find($data['item_id']);
        $orderId = $item->order_id;
        $item->delete();
        if ($this::where('order_id', $orderId)->first()) {
            $orderModel->updateOrder($orderId);
            $orderDetails = $orderModel->getOrderDetails($orderId);
        } else {
            $order = new Order();
            $order = $order::find($orderId);
            $order->delete();
            $orderDetails = [];
        }


        return ['success' => true, 'data' => $orderDetails];
    }

    public function manualOrderIem($orderId, $data)
    {
        $items = $data['items'];
        for ($i = 0; $i < count($items['medicines']); $i++) {
            if (!empty($items['medicines'][$i])) {
                $medicineStr = explode(' (', $items['medicines'][$i]);
                $medicine = new Medicine();

                $medicineData = $medicine->where('brand_name', 'like', trim($medicineStr[0]))->first();

                if (!empty($medicineData)) {
                    $itemInput = array(
                        'medicine_id' => $medicineData->id,
                        'company_id' => $data['company_id'],
                        'quantity' => $items['quantities'][$i],
                        'order_id' => $orderId,
                        //'exp_date' => $cartItem->exp_date,
                        // 'mfg_date' => $cartItem->mfg_date,
                        'batch_no' => $items['batches'][$i],
                        // 'dar_no' => $cartItem->dar_no,
                        //'unit_price' => $cartItem->unit_price,
                        // 'sub_total' => $cartItem->sub_total,
                        'total' => empty($items['totals'][$i]) ? 0 : $items['totals'][$i],
                        'mfg_date' => date("Y-m-d", strtotime($items['mfgs'][$i])),
                        'exp_date' => date("Y-m-d", strtotime($items['exps'][$i])),
                        // 'discount' => $cartItem->discount,
                    );
                    //var_dump($itemInput);exit;
                    $this::create($itemInput);
                }
            }
        }
        return true;
    }

    public function medicine()
    {
        return $this->belongsTo('App\Models\Medicine');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\MedicineCompany');
    }
}
