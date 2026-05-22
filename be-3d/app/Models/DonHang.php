<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonHang extends Model
{
    use HasFactory;

    protected $table = 'don_hang';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';

    protected $fillable = [
        'id_nguoi_dung',
        'ma_don_hang',
        'id_ma_giam_gia',
        'tong_tien_hang',
        'tien_duoc_giam',
        'phi_giao_hang',
        'tong_thanh_toan',
        'trang_thai',
        'dia_chi_giao_hang',
        'ghi_chu_khach_hang',
        'ly_do_huy',
    ];

    protected function casts(): array
    {
        return [
            'dia_chi_giao_hang' => 'array',
        ];
    }

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public function maGiamGia()
    {
        return $this->belongsTo(MaGiamGia::class, 'id_ma_giam_gia');
    }

    public function chiTiets()
    {
        return $this->hasMany(ChiTietDonHang::class, 'id_don_hang');
    }

    public function lichSuTrangThais()
    {
        return $this->hasMany(LichSuTrangThaiDonHang::class, 'id_don_hang');
    }

    public function thanhToan()
    {
        return $this->hasOne(ThanhToan::class, 'id_don_hang');
    }
}
