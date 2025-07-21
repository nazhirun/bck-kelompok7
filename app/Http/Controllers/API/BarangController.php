<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    /**
     * Menampilkan semua data barang
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $barang = Barang::all();
        
        return response()->json([
            'success' => true,
            'message' => 'Daftar data barang',
            'data' => $barang
        ]);
    }

    /**
     * Menampilkan detail data barang
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $barang = Barang::find($id);
        
        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Data barang tidak ditemukan'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Detail data barang',
            'data' => $barang
        ]);
    }
} 