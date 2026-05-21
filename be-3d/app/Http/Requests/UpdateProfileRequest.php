<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'ho_ten'       => 'required|string|max:255',
            'ngay_sinh'    => 'nullable|date|before:today',
            'gioi_thieu'   => 'nullable|string|max:1000',
            'anh_dai_dien' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'avatar'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'so_dien_thoai' => [
                'nullable',
                'string',
                'regex:/^[0-9]+$/',
                function ($attribute, $value, $fail) {
                    if (str_starts_with($value, '84')) {
                        if (strlen($value) !== 11) {
                            $fail('Số điện thoại không hợp lệ.');
                        }
                    } elseif (str_starts_with($value, '0')) {
                        if (strlen($value) !== 10) {
                            $fail('Số điện thoại không hợp lệ.');
                        }
                    } else {
                        $fail('Số điện thoại không hợp lệ.');
                    }
                }
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ho_ten.required'       => 'Họ tên không được để trống.',
            'ho_ten.string'         => 'Họ tên phải là chuỗi ký tự.',
            'ho_ten.max'            => 'Họ tên không vượt quá 255 ký tự.',
            
            'ngay_sinh.date'        => 'Ngày sinh không đúng định dạng.',
            'ngay_sinh.before'      => 'Ngày sinh phải là một ngày trong quá khứ.',
            
            'gioi_thieu.string'     => 'Giới thiệu phải là chuỗi ký tự.',
            'gioi_thieu.max'        => 'Giới thiệu không vượt quá 1000 ký tự.',
            
            'anh_dai_dien.image'    => 'File upload phải là hình ảnh.',
            'anh_dai_dien.mimes'    => 'Hình ảnh phải có định dạng jpeg, png, jpg, webp, gif.',
            'anh_dai_dien.max'      => 'Kích thước ảnh đại diện tối đa là 5MB.',

            'avatar.image'    => 'File upload phải là hình ảnh.',
            'avatar.mimes'    => 'Hình ảnh phải có định dạng jpeg, png, jpg, webp, gif.',
            'avatar.max'      => 'Kích thước ảnh đại diện tối đa là 5MB.',
        ];
    }
}
