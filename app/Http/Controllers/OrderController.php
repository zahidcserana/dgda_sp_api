<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Medicine;
use App\Models\MedicineCompany;
use App\Models\Order;
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
        $data = $request->all();
        $orderModel = new Order();
        $order = $orderModel->makeManualOrder($data);

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

    public function manualOrderList(Request $request)
    {
        $pageNo = $request->query('page_no') ?? 1;
        $limit = $request->query('limit') ?? 10;
        $offset = (($pageNo - 1) * $limit);
        $where = array();
        $where = array_merge(array(['is_manual', true]), $where);

        $orders = Order::where($where)
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return response()->json($orders);
    }
}
