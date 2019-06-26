<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    public function addItem($data)
    {
        $medicine = new Medicine();
        $medicineData = $medicine->where('brand_name', 'like', $data['medicine'])->first();

        $medicineCompany = new MedicineCompany();
        $companyData = $medicineCompany->where('company_name', 'like', $data['company'])->first();

        $item = array(
          'medicine_id' => $medicineData->id,
          'company_id' => $companyData->id,
          'quantity' => $data['quantity'],
          'cart_id' => $data['cart_id'],
          'unit_price' => 110,
          'sub_total' => 125,
        );

        $cartItem = CartItem::insertGetId($item);
        if ($cartItem) {
            return true;
        }
        return false;
    }
}
