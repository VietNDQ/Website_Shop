<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanhToan extends Model
{
    use HasFactory;

    protected $table = 'thanh_toan';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';

    protected $fillable = [
        'id_don_hang',
        'phuong_thuc',
        'ma_giao_dich',
        'so_tien',
        'trang_thai',
        'ngay_thanh_toan',
    ];

    protected function casts(): array
    {
        return [
            'ngay_thanh_toan' => 'datetime',
        ];
    }

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'id_don_hang');
    }
}
