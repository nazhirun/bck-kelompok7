<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terhubung dengan model ini
     *
     * @var string
     */
    protected $table = 'barang';

    /**
     * Atribut yang dapat diisi secara massal
     *
     * @var array
     */
    protected $fillable = [
        'nama',
        'harga',
        'stok',

    ];

    /**
     * Get the gambar attribute with full URL
     *
     * @param string $value
     * @return string
     */
    public function getGambarAttribute($value)
    {
        if (!$value) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Ambil hanya nama file saja
        $filename = basename($value);

        // Return URL publik langsung ke folder images
        return url('images/barang/' . $filename);
    }

    /**
     * Relasi ke tabel penjualan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function penjualan(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'barang_id');
    }
}
