<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PenjualanController extends Controller
{
    /**
     * Menampilkan daftar penjualan
     *
     * @return \Illuminate\Http\JsonResponse
     */
    
    /**
     * Menampilkan detail penjualan
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $penjualan = Penjualan::with('barang')->find($id);
        
        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Data Penjualan tidak ditemukan'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Detail Penjualan',
            'data' => $penjualan
        ]);
    }

    /**
     * Menyimpan penjualan baru
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'faktur' => 'required|string',
            'barang_id' => 'required|exists:barang,id',
            'qty' => 'required|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Cek apakah kombinasi faktur dan barang_id sudah ada
        $exists = Penjualan::where('faktur', $request->faktur)
                 ->where('barang_id', $request->barang_id)
                 ->exists();
                 
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Barang ini sudah ada dalam faktur yang sama',
                'errors' => [
                    'barang_id' => ['Barang sudah ditambahkan ke faktur ini']
                ]
            ], 422);
        }
        
        // Ambil data barang
        $barang = Barang::find($request->barang_id);
        
        // Validasi stok
        if ($barang->stok < $request->qty) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi',
            ], 400);
        }
        
        // Hitung total
        $total = $barang->harga * $request->qty;
        
        // Kurangi stok barang
        $barang->stok -= $request->qty;
        $barang->save();
        
        // Simpan penjualan
        $penjualan = Penjualan::create([
            'tanggal' => $request->tanggal,
            'faktur' => $request->faktur,
            'barang_id' => $request->barang_id,
            'qty' => $request->qty,
            'total' => $total
        ]);
        
        // Load relasi barang
        $penjualan->load('barang');
        
        return response()->json([
            'success' => true,
            'message' => 'Penjualan berhasil disimpan',
            'data' => $penjualan
        ], 201);
    }

    /**
     * Update data penjualan
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $penjualan = Penjualan::find($id);
        
        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Data Penjualan tidak ditemukan'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'tanggal' => 'sometimes|required|date',
            'faktur' => 'sometimes|required|string',
            'barang_id' => 'sometimes|required|exists:barang,id',
            'qty' => 'sometimes|required|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Jika ada perubahan pada faktur atau barang_id, periksa duplikasi
        if (($request->has('faktur') && $request->faktur !== $penjualan->faktur) || 
            ($request->has('barang_id') && $request->barang_id !== $penjualan->barang_id)) {
                
            $exists = Penjualan::where('faktur', $request->faktur ?? $penjualan->faktur)
                     ->where('barang_id', $request->barang_id ?? $penjualan->barang_id)
                     ->where('id', '!=', $id)
                     ->exists();
                     
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang ini sudah ada dalam faktur yang sama',
                    'errors' => [
                        'barang_id' => ['Barang sudah ditambahkan ke faktur ini']
                    ]
                ], 422);
            }
        }
        
        // Jika ada perubahan pada qty atau barang_id
        if (($request->has('qty') && $request->qty != $penjualan->qty) || 
            ($request->has('barang_id') && $request->barang_id != $penjualan->barang_id)) {
            
            // Kembalikan stok barang lama
            $barangLama = Barang::find($penjualan->barang_id);
            $barangLama->stok += $penjualan->qty;
            $barangLama->save();
            
            // Ambil dan update stok barang baru
            $barangBaru = Barang::find($request->barang_id ?? $penjualan->barang_id);
            
            // Cek stok
            if ($barangBaru->stok < ($request->qty ?? $penjualan->qty)) {
                // Kembalikan pengurangan stok yang sudah dilakukan
                $barangLama->stok -= $penjualan->qty;
                $barangLama->save();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi',
                ], 400);
            }
            
            // Kurangi stok barang baru
            $barangBaru->stok -= ($request->qty ?? $penjualan->qty);
            $barangBaru->save();
            
            // Hitung total baru
            $total = $barangBaru->harga * ($request->qty ?? $penjualan->qty);
        } else {
            // Total tetap
            $total = $penjualan->total;
        }
        
        // Update penjualan
        $penjualan->update([
            'tanggal' => $request->tanggal ?? $penjualan->tanggal,
            'faktur' => $request->faktur ?? $penjualan->faktur,
            'barang_id' => $request->barang_id ?? $penjualan->barang_id,
            'qty' => $request->qty ?? $penjualan->qty,
            'total' => $total
        ]);
        
        // Load relasi
        $penjualan->load('barang');
        
        return response()->json([
            'success' => true,
            'message' => 'Penjualan berhasil diupdate',
            'data' => $penjualan
        ]);
    }

    /**
     * Hapus data penjualan
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $penjualan = Penjualan::find($id);
        
        if (!$penjualan) {
            return response()->json([
                'success' => false,
                'message' => 'Data Penjualan tidak ditemukan'
            ], 404);
        }
        
        // Kembalikan stok barang
        $barang = Barang::find($penjualan->barang_id);
        $barang->stok += $penjualan->qty;
        $barang->save();
        
        // Hapus penjualan
        $penjualan->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Penjualan berhasil dihapus'
        ]);
    }
    
    /**
     * Get penjualan by faktur
     *
     * @param string $faktur
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByFaktur($faktur)
    {
        $penjualan = Penjualan::with('barang')
                    ->where('faktur', $faktur)
                    ->get();
        
        if ($penjualan->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data Penjualan tidak ditemukan'
            ], 404);
        }
        
        // Hitung total keseluruhan
        $grandTotal = $penjualan->sum('total');
        
        return response()->json([
            'success' => true,
            'message' => 'Detail Penjualan Berdasarkan Faktur',
            'data' => [
                'items' => $penjualan,
                'grand_total' => $grandTotal
            ]
        ]);
    }
} 