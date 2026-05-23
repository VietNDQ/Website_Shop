<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class NguoiDung extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable {
        HasApiTokens::createToken as traitCreateToken;
    }

    public function createToken(string $name, array $abilities = ['*'], \DateTimeInterface $expiresAt = null)
    {
        // Giới hạn 2 thiết bị: Nếu số token hiện tại >= 2, ta xóa bớt các token cũ nhất
        // Ví dụ: có 2 token, ta xóa 1 token cũ nhất. Có 3 token (lỗi tồn đọng), ta xóa 2.
        $tokensCount = $this->tokens()->count();
        if ($tokensCount >= 2) {
            $this->tokens()->orderBy('created_at', 'asc')
                ->limit($tokensCount - 1)
                ->get()
                ->each->delete();
        }

        return $this->traitCreateToken($name, $abilities, $expiresAt);
    }

    protected $table = 'nguoi_dung';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';

    protected $fillable = [
        'ho_ten',
        'email',
        'google_id',
        'zalo_id',
        'mat_khau',
        'vai_tro', // 1: quan_tri, 2: quan_ly, 3: khach_hang (Mặc định)
        'dang_hoat_dong',
        'dang_nhap_lan_cuoi_luc',
        'ngay_sinh',
        'gioi_thieu',
        'anh_dai_dien',
        'tong_chi_tieu',
        'diem_thanh_vien',
        'diem_cho_duyet',
        'hang_thanh_vien',
    ];

    protected $hidden = [
        'mat_khau',
    ];

    protected function casts(): array
    {
        return [
            'dang_hoat_dong' => 'boolean',
            'dang_nhap_lan_cuoi_luc' => 'datetime',
            'tao_luc' => 'datetime',
            'cap_nhat_luc' => 'datetime',
        ];
    }

    public function diaChis()
    {
        return $this->hasMany(DiaChiNguoiDung::class, 'id_nguoi_dung');
    }

    public function donHangs()
    {
        return $this->hasMany(DonHang::class, 'id_nguoi_dung');
    }

    public function danhGias()
    {
        return $this->hasMany(DanhGia::class, 'id_nguoi_dung');
    }

    public function yeuThichs()
    {
        return $this->belongsToMany(SanPham::class, 'yeu_thich', 'id_nguoi_dung', 'id_san_pham')
            ->withTimestamps('tao_luc', 'cap_nhat_luc');
    }

    public function daXems()
    {
        return $this->belongsToMany(SanPham::class, 'da_xem', 'id_nguoi_dung', 'id_san_pham')
            ->withTimestamps('tao_luc', 'cap_nhat_luc');
    }

    public static function updateMembership($userId)
    {
        $user = self::find($userId);
        if (!$user) return;

        $spent = (float) $user->tong_chi_tieu;
        $oldTier = $user->hang_thanh_vien ?? 'dong';

        $newTier = 'dong';
        if ($spent >= 15000000) {
            $newTier = 'vang';
        } elseif ($spent >= 5000000) {
            $newTier = 'bac';
        }

        if ($oldTier !== $newTier) {
            $user->hang_thanh_vien = $newTier;
            $user->save();

            $tierLabels = [
                'dong' => 'Đồng',
                'bac' => 'Bạc',
                'vang' => 'Vàng (S-VIP)'
            ];

            \App\Models\NhatKyHoatDong::create([
                'id_nguoi_dung' => $user->id,
                'ho_ten' => $user->ho_ten,
                'hanh_dong' => 'Thăng hạng thành viên',
                'mo_ta' => 'Khách hàng #' . $user->ho_ten . ' thay đổi hạng thành viên: ' . ($tierLabels[$oldTier] ?? $oldTier) . ' → ' . $tierLabels[$newTier],
                'loai_doi_tuong' => 'nguoi_dung',
                'id_doi_tuong' => $user->id,
                'mau_sac' => '#10b981'
            ]);
        }
    }
}
