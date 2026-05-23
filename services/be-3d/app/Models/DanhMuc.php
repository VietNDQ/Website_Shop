<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhMuc extends Model
{
    use HasFactory;

    protected $table = 'danh_muc';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';

    protected $fillable = [
        'id_danh_muc_cha',
        'emoji',
        'ten_danh_muc',
        'mo_ta',
        'duong_dan_mau',
        'thu_tu_hien_thi',
        'trang_thai',
    ];

    public function parent()
    {
        return $this->belongsTo(DanhMuc::class, 'id_danh_muc_cha');
    }

    public function children()
    {
        return $this->hasMany(DanhMuc::class, 'id_danh_muc_cha');
    }

    public function sanPhams()
    {
        return $this->hasMany(SanPham::class, 'id_danh_muc');
    }
}
