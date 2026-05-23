<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThongBao extends Model
{
    use HasFactory;

    protected $table = 'thong_bao';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = 'cap_nhat_luc';

    protected $fillable = [
        'tieu_de',
        'noi_dung',
        'loai',
        'duong_dan',
        'da_doc',
    ];

    protected $casts = [
        'da_doc' => 'boolean',
        'tao_luc' => 'datetime',
        'cap_nhat_luc' => 'datetime',
    ];

    /**
     * Helper to create a notification
     */
    public static function taoThongBao($tieu_de, $noi_dung, $loai, $duong_dan = '')
    {
        return self::create([
            'tieu_de' => $tieu_de,
            'noi_dung' => $noi_dung,
            'loai' => $loai,
            'duong_dan' => $duong_dan,
            'da_doc' => false,
        ]);
    }
}
