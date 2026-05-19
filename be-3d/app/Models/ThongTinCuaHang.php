<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongTinCuaHang extends Model
{
    use HasFactory;

    protected $table = 'thong_tin_cua_hang';

    protected $fillable = [
        'ten_thuong_hieu',
        'hotline',
        'email_ho_tro',
        'website',
        'dia_chi_kho',
        'mo_ta',
        'facebook',
        'instagram'
    ];
}
