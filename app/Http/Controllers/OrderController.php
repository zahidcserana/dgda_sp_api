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

    public function deleteItem(Request $request)
    {
        $data = $request->all();
        $cartItemModel = new OrderItem();
        $result = $cartItemModel->deleteItem($data);

        return response()->json($result);
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
        $query = $request->query();

        $pageNo = $request->query('page_no') ?? 1;
        $limit = $request->query('limit') ?? 100;
        $offset = (($pageNo - 1) * $limit);
        $where = array();
        $user = $request->auth;
        $where = array_merge(array(['orders.pharmacy_branch_id',$user->pharmacy_branch_id]), $where);
        $where = array_merge(array(['orders.is_manual', true]), $where);


        if (!empty($query['company_invoice'])) {
            $where = array_merge(array(['orders.company_invoice', 'LIKE', '%' . $query['company_invoice'] . '%']), $where);
        }
        if (!empty($query['batch_no'])) {
            $where = array_merge(array(['order_items.batch_no', 'LIKE', '%' . $query['batch_no'] . '%']), $where);
        }
        if (!empty($query['exp_type'])) {
            $where = $this->_getExpCondition($where, $query['exp_type']);
        }

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

            $aData['company_invoice'] = $item->company_invoice;

            $medicine = Medicine::findOrFail($item->medicine_id);
            $aData['medicine'] = ['id' => $medicine->id, 'brand_name' => $medicine->brand_name];

            $aData['exp_date'] = date("F, Y", strtotime($item->exp_date));
            //$aData['exp_date'] = date("F, Y", strtotime($item->exp_date));
            $aData['exp_status'] = $this->_getExpStatus($item->exp_date);
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

    private function _getExpStatus($date)
    {
        $expDate = date("F, Y", strtotime($date));

        $today = date('Y-m-d');
        $exp1M = date('Y-m-d', strtotime("+1 months", strtotime(date('Y-m-d'))));
        $exp3M = date('Y-m-d', strtotime("+3 months", strtotime(date('Y-m-d'))));
        if ($date < $today) {
            return 'EXP';
        } else if ($date >= $today && $date <= $exp1M) {
            return '1M';
        } else if ($date > $exp1M && $date <= $exp3M) {
            return '3M';
        } else {
            return 'OK';
        }
    }

    private function _getExpCondition($where, $expTpe)
    {
        $today = date('Y-m-d');
        $exp1M = date('Y-m-d', strtotime("+1 months", strtotime(date('Y-m-d'))));
        $exp3M = date('Y-m-d', strtotime("+3 months", strtotime(date('Y-m-d'))));
        if ($expTpe == 2) {
            $where = array_merge(array(
                ['order_items.exp_date', '>', $today],
                ['order_items.exp_date', '<', $exp1M]
            ), $where);
        } else if ($expTpe == 3) {
            $where = array_merge(array(
                ['order_items.exp_date', '>', $exp1M],
                ['order_items.exp_date', '<', $exp3M]
            ), $where);
        } else if ($expTpe == 1) {
            $where = array_merge(array(
                ['order_items.exp_date', '>', $exp3M]
            ), $where);
        } else if ($expTpe == 4) {
            $where = array_merge(array(['order_items.exp_date', '<', $today]), $where);
        }
        return $where;
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