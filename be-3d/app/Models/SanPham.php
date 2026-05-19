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
        'mo_ta',
        'tinh_trang',
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
}
