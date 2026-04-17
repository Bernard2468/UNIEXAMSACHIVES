<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function clearSessionMessages(Request $request)
    {
        $request->session()->forget(['success', 'error', 'warning', 'info']);
        return response()->json(['success' => true]);
    }
}
