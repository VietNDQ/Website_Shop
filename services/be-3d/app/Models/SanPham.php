<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SanPham extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'san_pham';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';
    const DELETED_AT = 'da_xoa_luc';

    protected $fillable = [
        'id_danh_muc',
        'ten_san_pham',
        'gia_co_ban',
        'gia_goc',
        'mo_ta',
        'tinh_trang',
        'so_luong_ton_kho',
    ];

    public function danhMuc()
    {
        return $this->belongsTo(DanhMuc::class, 'id_danh_muc');
    }

    public function hinhAnhs()
    {
        return $this->hasMany(HinhAnhSanPham::class, 'id_san_pham');
    }

    public function bienThes()
    {
        return $this->hasMany(BienTheSanPham::class, 'id_san_pham');
    }

    public function thuocTinhs()
    {
        return $this->hasMany(ThuocTinh::class, 'id_san_pham');
    }

    public function danhGias()
    {
        return $this->hasMany(DanhGia::class, 'id_san_pham');
    }

    public function yeuThichs()
    {
        return $this->hasMany(YeuThich::class, 'id_san_pham');
    }

    public function daXems()
    {
        return $this->hasMany(DaXem::class, 'id_san_pham');
    }
}
