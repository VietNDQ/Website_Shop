<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaiViet extends Model
{
    use HasFactory;

    protected $table = 'bai_viet';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';

    protected $fillable = [
        'tieu_de',
        'slug',
        'anh_dai_dien',
        'tom_tat',
        'noi_dung',
        'luot_xem',
        'loai',
        'trang_thai',
        'id_nguoi_dang',
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
        'luot_xem' => 'integer',
        'tao_luc' => 'datetime',
        'cap_nhat_luc' => 'datetime',
    ];

    /**
     * Relationship with user who posted
     */
    public function nguoiDang()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dang');
    }
}
