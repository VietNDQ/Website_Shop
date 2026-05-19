<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SanPhamRequestUpdate extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'           => 'required|numeric|exists:san_pham,id',
            'ten_san_pham' => 'required|string|max:255',
            'gia_co_ban'   => 'required|numeric',
            'id_danh_muc'  => 'nullable|numeric',
            'mo_ta'        => 'nullable|string',
            'sku'          => 'nullable|string|max:100',
            'so_luong_ton_kho' => 'nullable|numeric',
            'tinh_trang'   => 'nullable|string|in:active,out,hidden',
            'hinh_anh'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:9216',
            'hinh_anh_phu'   => 'nullable|array|max:10',
            'hinh_anh_phu.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:9216',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required'           => 'Không tìm thấy ID sản phẩm.',
            'id.exists'             => 'Sản phẩm không tồn tại trong CSDL.',
            'ten_san_pham.required' => 'Tên sản phẩm không được để trống.',
            'ten_san_pham.string'   => 'Tên sản phẩm phải là chuỗi ký tự.',
            'ten_san_pham.max'      => 'Tên sản phẩm không vượt quá 255 ký tự.',
            'gia_co_ban.required'   => 'Giá cơ bản không được để trống.',
            'gia_co_ban.numeric'    => 'Giá cơ bản phải là số.',
            'hinh_anh.image'        => 'File upload phải là hình ảnh.',
            'hinh_anh.mimes'        => 'Hình ảnh phải có định dạng jpeg, png, jpg, webp, gif.',
            'hinh_anh.max'          => 'Kích thước ảnh tối đa là 9MB.',
        ];
    }
}
