<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NguoiDungVoucher extends Model
{
    use HasFactory;

    protected $table = 'nguoi_dung_voucher';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';

    protected $fillable = [
        'id_nguoi_dung',
        'id_ma_giam_gia',
        'trang_thai', // 'unused', 'used'
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public function maGiamGia()
    {
        return $this->belongsTo(MaGiamGia::class, 'id_ma_giam_gia');
    }
}
