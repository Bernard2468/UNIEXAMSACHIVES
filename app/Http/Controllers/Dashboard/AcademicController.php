<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Academic;

class AcademicController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|string|max:255',
        ]);

        Academic::create($request->all());

        return redirect()->route('dashboard')->with('success', 'Academic Year added successfully.');
    }
}
