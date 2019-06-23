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
        $medicines = Medicine::where('medicine_brand_name', 'like', '%' . $str . '%')->get();
        return response()->json($medicines);
    }
}
