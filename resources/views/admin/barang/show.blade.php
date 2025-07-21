@extends('admin.layouts.app')

@section('title', 'Detail Barang')

@section('page_title', 'Detail Barang')

@section('content')
<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Gambar Barang -->
        <div class="w-full md:w-1/3">
            @if($barang->gambar)
                <img src="{{ asset($barang->gambar) }}" alt="{{ $barang->nama }}" class="w-full h-auto object-cover rounded-lg shadow-md">
            @else
                <div class="w-full h-64 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                    <span class="text-gray-500 dark:text-gray-400">Tidak ada gambar</span>
                </div>
            @endif
        </div>
        
        <!-- Informasi Barang -->
        <div class="w-full md:w-2/3">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $barang->nama }}</h1>
                <span class="px-3 py-1 text-sm font-medium text-white bg-blue-700 rounded-full">{{ $barang->kategori }}</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Harga</h2>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($barang->harga, 0, ',', '.') }}</p>
                </div>
                
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Stok</h2>
                    <p class="text-lg">
                        @if($barang->stok > 10)
                            <span class="text-green-600 dark:text-green-400">{{ $barang->stok }} tersedia</span>
                        @elseif($barang->stok > 0)
                            <span class="text-yellow-600 dark:text-yellow-400">{{ $barang->stok }} tersedia (Terbatas)</span>
                        @else
                            <span class="text-red-600 dark:text-red-400">Stok Habis</span>
                        @endif
                    </p>
                </div>
            </div>
            
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Deskripsi</h2>
                <div class="text-gray-700 dark:text-gray-300">
                    {!! nl2br(e($barang->keterangan)) ?: '<span class="text-gray-500 dark:text-gray-400">Tidak ada deskripsi</span>' !!}
                </div>
            </div>
            
            <div class="mt-8 flex gap-2">
                <a href="{{ route('admin.barang.edit', $barang->id) }}" class="text-white bg-yellow-600 hover:bg-yellow-700 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-yellow-500 dark:hover:bg-yellow-600 dark:focus:ring-yellow-700">Edit</a>
                
                <form action="{{ route('admin.barang.destroy', $barang->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">Hapus</button>
                </form>
                
                <a href="{{ route('admin.barang.index') }}" class="text-gray-900 bg-white border border-gray-300 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection 