<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $fillable = ['nama'];
    
    /**
     * Relasi dengan barang
     */
    public function barang()
    {
        return $this->hasMany(Barang::class, 'kategori_id', 'id');
    }
}
