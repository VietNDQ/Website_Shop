<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NhatKyTonKho extends Model
{
    use HasFactory;

    protected $table = 'nhat_ky_ton_kho';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_bien_the',
        'so_luong_thay_doi',
        'loai_giao_dich',
        'ma_tham_chieu',
        'ghi_chu',
    ];

    public function bienThe()
    {
        return $this->belongsTo(BienTheSanPham::class, 'id_bien_the');
    }
}
