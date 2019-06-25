<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Medicine;
use App\Models\MedicineCompany;
use Illuminate\Http\Request;

class CartsController extends Controller
{
    public function addToCart(Request $request)
    {
        $data = $request->all();

//        $this->validate($request, [
//            'medicine' => 'required',
//            'company' => 'required',
//            //'quantity' => 'required|number'
//        ]);

        $data['medicine_id'] = $this->_getMedicineId($request->input('medicine'));
        $data['company_id'] = $this->_getCompanyId($request->input('company'));
        $cartModel = new Cart();
        $cart = $cartModel->AddToCart($data);

        return response()->json($cart);

    }

    private function _getMedicineId($medicineName)
    {
        $medicineData = Medicine::where('brand_name', 'like', $medicineName)->first();

        return !empty($medicineData) ? $medicineData->id : '';
    }

    private function _getCompanyId($companyName)
    {
        $companyData = MedicineCompany::where('company_name', 'like', $companyName)->first();

        return !empty($companyData) ? $companyData->id : '';
    }
}
