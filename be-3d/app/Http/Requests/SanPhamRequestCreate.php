<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SanPhamRequestCreate extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Đã check quyền ở Middleware
    }

    public function rules(): array
    {
        return [
            'ten_san_pham' => 'required|string|max:255',
            'gia_co_ban'   => 'required|numeric',
            'gia_goc'      => 'nullable|numeric',
            'id_danh_muc'  => 'nullable|numeric',
            'mo_ta'        => 'nullable|string',
            'sku'          => 'required|string|max:100|unique:bien_the_san_pham,ma_kho',
            'so_luong_ton_kho' => 'required|numeric',
            'tinh_trang'   => 'nullable|string|in:active,out,hidden',
            'hinh_anh'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:9216',
            'hinh_anh_phu'   => 'nullable|array|max:10',
            'hinh_anh_phu.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:9216',
        ];
    }

    public function messages(): array
    {
        return [
            'ten_san_pham.required' => 'Tên sản phẩm không được để trống.',
            'ten_san_pham.string'   => 'Tên sản phẩm phải là chuỗi ký tự.',
            'ten_san_pham.max'      => 'Tên sản phẩm không vượt quá 255 ký tự.',
            'gia_co_ban.required'   => 'Giá cơ bản không được để trống.',
            'gia_co_ban.numeric'    => 'Giá cơ bản phải là số.',
            'sku.required'          => 'Mã SKU không được để trống.',
            'sku.unique'            => 'Mã SKU này đã tồn tại trong hệ thống.',
            'so_luong_ton_kho.required' => 'Số lượng tồn kho không được để trống.',
            'hinh_anh.image'        => 'File upload phải là hình ảnh.',
            'hinh_anh.mimes'        => 'Hình ảnh phải có định dạng jpeg, png, jpg, webp, gif.',
            'hinh_anh.max'          => 'Kích thước ảnh tối đa là 9MB.',
        ];
    }
}
