<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaGiamGia extends Model
{
    use HasFactory;

    protected $table = 'ma_giam_gia';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = null;

    protected $fillable = [
        'ma_code',
        'loai_giam_gia',
        'gia_tri_giam',
        'don_hang_toi_thieu',
        'muc_giam_toi_da',
        'ngay_bat_dau',
        'ngay_ket_thuc',
        'gioi_han_su_dung',
        'so_lan_da_dung',
        'ngan_sach',
        'ngan_sach_da_dung',
        'hinh_thuc_phat_hanh',
        'dang_hoat_dong',
    ];

    protected function casts(): array
    {
        return [
            'ngay_bat_dau'   => 'datetime',
            'ngay_ket_thuc'  => 'datetime',
            'dang_hoat_dong' => 'boolean',
        ];
    }
}
