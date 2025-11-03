<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index()
    {
        return Audit::with('user')->latest()->get();
    }

    public function clear()
    {
        Audit::truncate();
        return response()->json(['message' => 'Historique effacé']);
    }
}

