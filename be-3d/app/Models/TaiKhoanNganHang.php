<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaiKhoanNganHang extends Model
{
    use HasFactory;

    protected $table = 'tai_khoan_ngan_hang';

    protected $fillable = [
        'bank_id',
        'bank_account_no',
        'bank_account_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
