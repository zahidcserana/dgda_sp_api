<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $guarded = [];

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
            $aData['power'] = $item->power;
            $aData['quantity'] = $item->quantity;
            $aData['batch_no'] = $item->batch_no;
            $aData['tax'] = $item->tax;
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

    /** Manual Order */

    public function makeManualOrder($data)
    {
        $pharmacy_branch_id = 1;
        $input = array(
            'pharmacy_branch_id' => $pharmacy_branch_id,
            'is_manual' => true,
            'purchase_date' => empty($data['purchase_date']) ? date('Y-m-d') : $data['purchase_date'],
            'company_invoice' => $data['company_invoice'],
            'discount' => empty($data['discount']) ? 0 : $data['discount'],
        );

        $orderId = $this::insertGetId($input);

        $this->_createOrderInvoice($orderId, $pharmacy_branch_id);

        $orderItemModel = new OrderItem();
        if ($orderItemModel->manualOrderIem($orderId, $data)) {
            $this->updateOrder($orderId);

            return ['success' => true, 'data' => $this->getOrderDetails($orderId)];
        }
        return ['success' => false, 'error' => 'Something went wrong!'];
    }

    public function updateOrder($orderId)
    {
        $orderItem = new OrderItem();
        $orderItem = $orderItem
            ->select(DB::raw('
            SUM(sub_total) as total_sub_total,
            SUM(total) as total_amount, 
            SUM(quantity) as total_quantity, 
            SUM(tax) as total_tax'))
            ->where('order_id', $orderId)
            ->first();

        $order = $this::findOrFail($orderId);

        $data = array(
            'sub_total' => $orderItem->total_sub_total ?? 0,
            'quantity' => $orderItem->total_quantity,
            'total_amount' => $orderItem->total_amount,
            'tax' => $orderItem->total_tax,
            'total_payble_amount' => ($orderItem->total_amount + $orderItem->total_tax) - $order->discount,
        );
        $order->update($data);
        return true;
    }

    /** ************* */

    /** Relationship */
    public function items()
    {
        return $this->hasMany('App\Models\OrderItem');
    }

    public function PharmacyBranch()
    {
        return $this->belongsTo('App\Models\PharmacyBranch');
    }
    /** **** **** **** **** **** **** */
}
