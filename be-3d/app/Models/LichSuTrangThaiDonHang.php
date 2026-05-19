<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LichSuTrangThaiDonHang extends Model
{
    use HasFactory;

    protected $table = 'lich_su_trang_thai_don_hang';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_don_hang',
        'trang_thai',
        'ghi_chu',
    ];

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'id_don_hang');
    }
}
