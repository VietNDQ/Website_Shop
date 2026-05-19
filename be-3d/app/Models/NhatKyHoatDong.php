<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NhatKyHoatDong extends Model
{
    use HasFactory;

    protected $table = 'nhat_ky_hoat_dong';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = null; // Bảng này chỉ log, không update

    protected $fillable = [
        'id_nguoi_dung',
        'ten_nguoi_dung',
        'hanh_dong',
        'mau_sac',
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }
}
