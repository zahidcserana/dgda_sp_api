<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Medicine;
use App\Models\MedicineCompany;
use Illuminate\Http\Request;
use Validator;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $data = $request->all();

        $this->validate($request, [
            'medicine' => 'required',
            'company' => 'required',
            'quantity' => 'required'
        ]);
        $cartModel = new Cart();
        $cart = $cartModel->AddToCart($data);

        return response()->json($cart);

    }

    public function view($cartToken)
    {

        $cart = Cart::where('token', $cartToken)->first();
        $cartModel = new Cart();
        $result = $cartModel->getCartDetails($cart->id);

        return response()->json($result);
    }

    public function quantityUpdate(Request $request)
    {
        $data = $request->all();
        $cartModel = new Cart();
        $cartUpdate = $cartModel->quantityUpdate($data);
        
        return response()->json($cartUpdate);
    }

    public function deleteItem(Request $request)
    {
        $data = $request->all();
        $cartItemModel = new CartItem();
        $result = $cartItemModel->deleteItem($data);

        return response()->json($result);
    }
}
