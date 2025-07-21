<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BarangController extends Controller
{

    public function index()
    {
        $barang = Barang::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.barang.index', compact('barang'));
    }

    public function create()
    {
        $kategori = Kategori::orderBy('nama')->get();
        return view('admin.barang.create', compact('kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
            'kategori_id' => 'required|exists:kategori,id',
            'keterangan' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except(['gambar', 'kategori_id']);
        
        $kategori = Kategori::findOrFail($request->kategori_id);
        $data['kategori'] = $kategori->nama;
        
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = Str::slug($request->nama) . '-' . time() . '.' . $file->getClientOriginalExtension();
            
            // Pastikan direktori ada
            $uploadPath = public_path('images/barang');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0777, true);
            }
            $file->move($uploadPath, $filename);
            $data['gambar'] = '/images/barang/' . $filename;
        }

        Barang::create($data);

        return redirect()->route('admin.barang.index')
            ->with('success', 'Barang berhasil ditambahkan!');
    }

    public function show(Barang $barang)
    {
        return view('admin.barang.show', compact('barang'));
    }


    public function edit(Barang $barang)
    {
        $kategori = Kategori::orderBy('nama')->get();
        return view('admin.barang.edit', compact('barang', 'kategori'));
    }

    public function update(Request $request, Barang $barang)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
            'kategori_id' => 'required|exists:kategori,id',
            'keterangan' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except(['gambar', 'kategori_id']);

        $kategori = Kategori::findOrFail($request->kategori_id);
        $data['kategori'] = $kategori->nama;
        
        if ($request->hasFile('gambar')) {
            if ($barang->gambar) {
                $oldPath = public_path(ltrim($barang->gambar, '/'));
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }
            
            $file = $request->file('gambar');
            $filename = Str::slug($request->nama) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $uploadPath = public_path('images/barang');
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0777, true);
            }
            
            $file->move($uploadPath, $filename);
            
            $data['gambar'] = '/images/barang/' . $filename;
        }

        $barang->update($data);

        return redirect()->route('admin.barang.index')
            ->with('success', 'Barang berhasil diperbarui!');
    }

    public function destroy(Barang $barang)
    {
        if ($barang->gambar) {
            $oldPath = public_path(ltrim($barang->gambar, '/'));
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
        }
        
        $barang->delete();

        return redirect()->route('admin.barang.index')
            ->with('success', 'Barang berhasil dihapus!');
    }
} 