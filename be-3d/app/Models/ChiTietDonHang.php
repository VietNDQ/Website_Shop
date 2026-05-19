<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietDonHang extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_don_hang';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_don_hang',
        'id_bien_the',
        'ten_bien_the_luc_mua',
        'so_luong',
        'don_gia',
        'thanh_tien',
    ];

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'id_don_hang');
    }

    public function bienThe()
    {
        return $this->belongsTo(BienTheSanPham::class, 'id_bien_the');
    }
}
