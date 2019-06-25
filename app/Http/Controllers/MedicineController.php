<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicineController extends Controller
{
    public function search(Request $request)
    {
        $str = $request->input('search');
        $medicines = Medicine::where('brand_name', 'like', '%' . $str . '%')
            ->inRandomOrder()
            ->limit(10)
            ->get();
        $data = array();
        foreach ($medicines as $medicine) {
            $data[] = $medicine->brand_name.' ('.$medicine->medicine_id.') ';
        }
        return response()->json($data);
    }
}
