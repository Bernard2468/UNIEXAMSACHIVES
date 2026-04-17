<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /**
     * Display a listing of licenses (view-only for regular users/admins)
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $licenses = License::orderBy('name')->orderBy('created_at')->paginate($perPage)->withQueryString();
        
        return view('admin.licenses.index', compact('licenses'));
    }
}
