<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThuLienHe extends Model
{
    use HasFactory;

    protected $table = 'thu_lien_he';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';

    protected $fillable = [
        'id_nguoi_dung',
        'ho_ten',
        'email',
        'so_dien_thoai',
        'tieu_de',
        'noi_dung',
        'trang_thai',
        'phan_hoi',
    ];

    protected $casts = [
        'trang_thai' => 'integer',
        'tao_luc' => 'datetime',
        'cap_nhat_luc' => 'datetime',
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }
}
