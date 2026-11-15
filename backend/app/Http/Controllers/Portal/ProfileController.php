<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;

        return view('portal.profile.index', compact('employee'));
    }
}