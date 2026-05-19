<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class NguoiDung extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'nguoi_dung';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';

    protected $fillable = [
        'ho_ten',
        'email',
        'mat_khau',
        'vai_tro', // 1: quan_tri, 2: quan_ly, 3: khach_hang (Mặc định)
        'dang_hoat_dong',
        'dang_nhap_lan_cuoi_luc',
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
}
