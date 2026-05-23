<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BienTheSanPham extends Model
{
    use HasFactory;

    protected $table = 'bien_the_san_pham';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';

    protected $fillable = [
        'id_san_pham',
        'ma_kho',
        'thuoc_tinh',
        'hinh_anh',
        'gia_ban',
        'gia_goc',
        'so_luong_ton_kho',
    ];

    protected function casts(): array
    {
        return [
            'thuoc_tinh' => 'array',
        ];
    }

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'id_san_pham');
    }

    public function nhatKyTonKhos()
    {
        return $this->hasMany(NhatKyTonKho::class, 'id_bien_the');
    }
}
