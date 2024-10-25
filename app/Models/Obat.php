<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obat extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
        'stok',
        'harga',
        'tanggal_kadaluarsa',
    ];

    public function penjualans()
    {
        return $this->hasMany(Penjualan::class);
    }
}
