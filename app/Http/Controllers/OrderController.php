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
        if (Order::where('token', $request->token)->update($updateQuery)) {
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }
}
