<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThuocTinh extends Model
{
    use HasFactory;

    protected $table = 'thuoc_tinh';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';

    protected $fillable = [
        'id_san_pham',
        'ten_thuoc_tinh',
        'gia_tri',
    ];

    protected function casts(): array
    {
        return [
            'gia_tri' => 'array',
        ];
    }

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'id_san_pham');
    }
}
