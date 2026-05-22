<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhGia extends Model
{
    use HasFactory;

    protected $table = 'danh_gia';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';

    protected $fillable = [
        'id_nguoi_dung',
        'id_san_pham',
        'id_bien_the',
        'id_chi_tiet_don_hang',
        'so_sao',
        'binh_luan',
        'phan_hoi_admin',
        'trang_thai',
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'id_san_pham');
    }

    public function bienThe()
    {
        return $this->belongsTo(BienTheSanPham::class, 'id_bien_the');
    }

    public function chiTietDonHang()
    {
        return $this->belongsTo(ChiTietDonHang::class, 'id_chi_tiet_don_hang');
    }

    public function anhs()
    {
        return $this->hasMany(AnhDanhGia::class, 'id_danh_gia');
    }
}
