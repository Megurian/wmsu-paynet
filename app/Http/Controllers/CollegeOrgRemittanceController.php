<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CollegeOrgRemittanceController extends Controller
{
    public function index()
    {
        return view('college_org.remittance');
    }

}
