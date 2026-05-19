<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    use HasFactory;

    protected $table = 'flash_sale';

    protected $fillable = [
        'ten_san_pham',
        'emoji',
        'gia_goc',
        'gia_flash',
        'phan_tram_giam',
        'thoi_gian_bat_dau',
        'thoi_gian_ket_thuc',
        'dang_hoat_dong',
    ];

    protected $casts = [
        'dang_hoat_dong'     => 'boolean',
        'thoi_gian_bat_dau'  => 'datetime',
        'thoi_gian_ket_thuc' => 'datetime',
    ];
}
