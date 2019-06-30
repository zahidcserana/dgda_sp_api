<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function districtList()
    {
        $districts = DB::table('districts')->get();

        return response()->json($districts);
    }

    public function CompanyList()
    {
        $companies = DB::table('medicine_companies')->get();

        return response()->json($companies);
    }


}
