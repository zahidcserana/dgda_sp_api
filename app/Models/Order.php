<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    public function makeOrder($data)
    {
        $cartModel = new Cart();
        $cartData = $cartModel->where('token', $data['token'])->first();
        if (empty($cartData)) {
            return ['success' => false, 'error' => 'Something went wrong!'];
        }
        $input = array(
            'pharmacy_branch_id' => $cartData->pharmacy_branch_id,
            'quantity' => $cartData->quantity,
            'sub_total' => $cartData->sub_total,
            'tax' => $cartData->tax,
            'discount' => $cartData->discount,
            'remarks' => $cartData->remarks,
        );

        $orderId = $this::insertGetId($input);

        $this->_createOrderInvoice($orderId, $cartData->pharmacy_branch_id);

        $orderItemModel = new OrderItem();
        $orderItemModel->addItem($orderId, $cartData->id);
        return ['success' => true];
    }

    private function _createOrderInvoice($orderId, $pharmacy_branch_id)
    {
        $pharmacyBranchModel = new PharmacyBranch();
        $pharmacyBranch = $pharmacyBranchModel->where('id', $pharmacy_branch_id)->first();
        $invoice = $orderId . substr($pharmacyBranch->branch_mobile, -4) . Carbon::now()->timestamp;
        $this->where('id', $orderId)->update(['invoice' => $invoice]);
        return;
    }

    public function getOrderDetails($orderId)
    {
        $order = $this::find($orderId);

        $orderItems = $order->items()->get();
        $data = array();
        $data['token'] = $order->token;
        $data['pharmacy_branch_id'] = $order->pharmacy_branch_id;
        $data['sub_total'] = $order->sub_total;
        $data['tax'] = $order->tax;
        $data['discount'] = $order->discount;
        $data['remarks'] = $order->remarks;
        $items = array();
        foreach ($orderItems as $item) {
            $aData = array();
            $aData['id'] = $item->id;
            $aData['medicine_id'] = $item->medicine_id;
            $aData['quantity'] = $item->quantity;
            $aData['batch_no'] = $item->batch_no;
            $aData['dar_no'] = $item->dar_no;
            $aData['unit_price'] = $item->unit_price;
            $aData['sub_total'] = $item->sub_total;
            $aData['discount'] = $item->discount;

            $medicine = $item->medicine;
            $aData['medicine'] = ['id' => $medicine->id, 'brand_name' => $medicine->brand_name];
            $items[] = $aData;
        }
        $data['order_items'] = $items;

        return $data;
    }

    public function items()
    {
        return $this->hasMany('App\Models\OrderItem');
    }

    public function PharmacyBranch()
    {
        return $this->belongsTo('App\Models\PharmacyBranch');
    }
}
