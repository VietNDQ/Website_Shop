<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietGioHang extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_gio_hang';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';

    protected $fillable = [
        'id_gio_hang',
        'id_bien_the',
        'so_luong',
    ];

    public function gioHang()
    {
        return $this->belongsTo(GioHang::class, 'id_gio_hang');
    }

    public function bienThe()
    {
        return $this->belongsTo(BienTheSanPham::class, 'id_bien_the');
    }
}
