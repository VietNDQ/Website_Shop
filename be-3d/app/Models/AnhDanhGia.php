<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnhDanhGia extends Model
{
    use HasFactory;

    protected $table = 'anh_danh_gia';

    const CREATED_AT = 'tao_luc';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_danh_gia',
        'duong_dan_anh',
    ];

    public function danhGia()
    {
        return $this->belongsTo(DanhGia::class, 'id_danh_gia');
    }
}
