<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaChiNguoiDung extends Model
{
    use HasFactory;

    protected $table = 'dia_chi_nguoi_dung';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';

    protected $fillable = [
        'id_nguoi_dung',
        'so_dien_thoai',
        'dia_chi_chi_tiet',
        'thanh_pho',
        'quan_huyen',
        'la_mac_dinh',
    ];

    protected function casts(): array
    {
        return [
            'la_mac_dinh' => 'boolean',
            'tao_luc' => 'datetime',
            'cap_nhat_luc' => 'datetime',
        ];
    }

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }
}
