<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'obat_id',
        'jumlah',
        'total_harga',
        'tanggal_penjualan',
    ];

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }
}
