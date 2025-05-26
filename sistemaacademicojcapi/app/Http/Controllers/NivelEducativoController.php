<?php

namespace App\Http\Controllers;

use App\Models\NivelEducativo;

class NivelEducativoController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => NivelEducativo::all()
        ], 200);
    }
}
