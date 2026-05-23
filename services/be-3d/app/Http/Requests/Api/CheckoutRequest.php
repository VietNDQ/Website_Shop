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
            'dung_xu' => 'nullable|boolean',
            'id_dia_chi' => 'required_without:ho_ten|nullable|exists:dia_chi_nguoi_dung,id',
            'phuong_thuc_thanh_toan' => 'required|in:tien_mat,vnpay,momo,the_tin_dung,chuyen_khoan',
            'ghi_chu_khach_hang' => 'nullable|string',
            
            // Guest checkout fields
            'ho_ten' => 'required_without:id_dia_chi|nullable|string|max:255',
            'so_dien_thoai' => 'required_without:id_dia_chi|nullable|string|max:20',
            'dia_chi_chi_tiet' => 'required_without:id_dia_chi|nullable|string|max:255',
            'thanh_pho' => 'required_without:id_dia_chi|nullable|string|max:255',
            'quan_huyen' => 'required_without:id_dia_chi|nullable|string|max:255',
            'phuong_xa' => 'required_without:id_dia_chi|nullable|string|max:255',
        ];
    }
}
