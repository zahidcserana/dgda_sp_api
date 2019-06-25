<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function search(Request $request)
    {
        $str = $request->input('search');
        $medicines = Medicine::where('brand_name', 'like', '%' . $str . '%')
            ->orWhere('id', $str)
            ->inRandomOrder()
            ->limit(10)
            ->get();
        $data = array();
        foreach ($medicines as $medicine) {
            $data[] = $medicine->brand_name;
        }
        return response()->json($data);
    }


}
