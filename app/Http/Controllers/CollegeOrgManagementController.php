<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CollegeOrgManagementController extends Controller
{
     public function index()
    {
        return view('college_org.organization_management.index');
    }
}
