<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Medicine;
use App\Models\MedicineCompany;
use Illuminate\Http\Request;
use Validator;

class CartsController extends Controller
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
}
