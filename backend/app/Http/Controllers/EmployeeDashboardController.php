<?php

namespace App\Http\Controllers;

/**
 * Old team dashboard address. The team portal replaced it, so just send
 * people to the portal dashboard (keeps old bookmarks working).
 */
class EmployeeDashboardController extends Controller
{
    public function index()
    {
        return redirect()->route('portal.dashboard');
    }
}
