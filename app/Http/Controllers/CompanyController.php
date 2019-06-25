<?php

namespace App\Http\Controllers;

use App\Models\MedicineCompany;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = MedicineCompany::all();

        $data = array();
        foreach ($companies as $company) {
            $data[] = $company->company_name;
        }
        return response()->json($data);
    }
}
