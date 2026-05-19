<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HinhAnhSanPham extends Model
{
    use HasFactory;

    protected $table = 'hinh_anh_san_pham';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_san_pham',
        'duong_dan_anh',
        'la_anh_dai_dien',
        'thu_tu_hien_thi',
    ];

    protected function casts(): array
    {
        return [
            'la_anh_dai_dien' => 'boolean',
        ];
    }

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'id_san_pham');
    }
}
