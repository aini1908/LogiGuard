<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortController extends Controller
{
    public function index()
    {
        // Ambil semua data pelabuhan dunia dari tabel ports
        $ports = DB::table('ports')->get();

        // Kembalikan dalam bentuk format JSON API sesuai instruksi dosen
        return response()->json([
            'status' => 'success',
            'data' => $ports
        ], 200);
    }
}
