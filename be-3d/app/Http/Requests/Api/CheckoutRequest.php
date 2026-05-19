<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array',
            'items.*.id_bien_the' => 'required|exists:bien_the_san_pham,id',
            'items.*.so_luong' => 'required|integer|min:1',
            'ma_giam_gia' => 'nullable|string',
            'id_dia_chi' => 'required|exists:dia_chi_nguoi_dung,id',
            'phuong_thuc_thanh_toan' => 'required|in:tien_mat,vnpay,momo,the_tin_dung',
            'ghi_chu_khach_hang' => 'nullable|string',
        ];
    }
}
