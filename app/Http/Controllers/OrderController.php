<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Medicine;
use App\Models\MedicineCompany;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Validator;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        $data = $request->all();

        $this->validate($request, [
            'token' => 'required',
        ]);
        $orderModel = new Order();
        $order = $orderModel->makeOrder($data);

        return response()->json($order);

    }

    public function manualOrder(Request $request)
    {
        $user = $request->auth;

        $data = $request->all();
        $orderModel = new Order();
        $order = $orderModel->makeManualOrder($data, $user);

        return response()->json($order);
    }

    public function view($orderToken)
    {
        $order = Order::where('token', $orderToken)->first();
        $orderModel = new Order();
        return response()->json($orderModel->getOrderDetails($order->id));
    }

    public function index()
    {
        $orders = Order::orderBy('id', 'desc')->get();
        foreach ($orders as $order) {
            $order->pharmacy_branch = $order->PharmacyBranch;
        }

        return response()->json($orders);
    }

    public function update(Request $request)
    {
        $updateQuery = $request->all();
        $updateQuery['updated_at'] = date('Y-m-d H:i:s');
        $orderStatus = Order::where('token', $request->token)->first()->status;
//        if ($orderStatus == 'ACCEPTED') {
//            return response()->json(['success' => false, 'status' => $orderStatus]);
//        }

        if (Order::where('token', $request->token)->update($updateQuery)) {
            return response()->json(['success' => true, 'status' => Order::where('token', $request->token)->first()->status]);
        }
        return response()->json(['success' => false, 'status' => $orderStatus]);
    }

    public function statusUpdate(Request $request)
    {
        $updateQuery = $request->all();
        $updateQuery['updated_at'] = date('Y-m-d H:i:s');

        $changeStatus = OrderItem::find($request->item_id)->is_status_updated;
        if ($changeStatus) {
            return response()->json(['success' => false, 'error' => 'Status Already changed']);
        }
        unset($updateQuery['item_id']);
        $updateQuery['is_status_updated'] = true;
        if (OrderItem::find($request->item_id)->update($updateQuery)) {
            return response()->json(['success' => true, 'status' => OrderItem::find($request->item_id)->status]);
        }
        return response()->json(['success' => false, 'error' => 'Already changed']);
    }

    public function manualOrderList(Request $request)
    {
        $pageNo = $request->query('page_no') ?? 1;
        $limit = $request->query('limit') ?? 100;
        $offset = (($pageNo - 1) * $limit);
        $where = array();
        $where = array_merge(array(['orders.is_manual', true]), $where);
        $query = Order::where($where)
            ->join('order_items', 'orders.id', '=', 'order_items.order_id');
        $total = $query->count();
        $orders = $query
            ->orderBy('orders.id', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        $orderData = array();
        foreach ($orders as $item) {
            //$items = $order->items()->get();

            $aData = array();
            $aData['id'] = $item->id;
            $aData['order_id'] = $item->order_id;

            $company = MedicineCompany::findOrFail($item->company_id);
            $aData['company'] = ['id' => $company->id, 'name' => $company->company_name];

            $aData['invoice'] = $item->invoice;

            $medicine = Medicine::findOrFail($item->medicine_id);
            $aData['medicine'] = ['id' => $medicine->id, 'brand_name' => $medicine->brand_name];

            $aData['exp_date'] = date("F, Y", strtotime($item->exp_date));
            $aData['mfg_date'] = date("F, Y", strtotime($item->mfg_date));

            //$aData['mfg_date'] = $item->mfg_date;
            $aData['batch_no'] = $item->batch_no;
            $aData['quantity'] = $item->quantity;
            $aData['status'] = $item->status;

            $orderData[] = $aData;

        }

        $data = array(
            'total' => $total,
            'data' => $orderData,
            'page_no' => $pageNo,
            'limit' => $limit,
        );

        return response()->json($data);
    }

    public function manualOrderList_old(Request $request)
    {
        $pageNo = $request->query('page_no') ?? 1;
        $limit = $request->query('limit') ?? 100;
        $offset = (($pageNo - 1) * $limit);
        $where = array();
        $where = array_merge(array(['is_manual', true]), $where);
        $query = Order::where($where);
        $total = $query->count();
        $orders = $query
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        $orderData = array();
        foreach ($orders as $order) {
            $items = $order->items()->get();
            foreach ($items as $item) {
                $aData = array();
                $aData['id'] = $item->id;
                $aData['order_id'] = $item->order_id;

                $company = $item->company;
                $aData['company'] = ['id' => $company->id, 'name' => $company->company_name];

                $aData['invoice'] = $order->invoice;

                $medicine = $item->medicine;
                $aData['medicine'] = ['id' => $medicine->id, 'brand_name' => $medicine->brand_name];

                $aData['exp_date'] = date("F, Y", strtotime($item->exp_date));
                $aData['mfg_date'] = date("F, Y", strtotime($item->mfg_date));

                //$aData['mfg_date'] = $item->mfg_date;
                $aData['batch_no'] = $item->batch_no;
                $aData['quantity'] = $item->quantity;
                $aData['status'] = $item->status;

                $orderData[] = $aData;
            }

        }

        $data = array(
            'total' => $total,
            'data' => $orderData,
            'page_no' => $pageNo,
            'limit' => $limit,
        );

        return response()->json($data);
    }
}
