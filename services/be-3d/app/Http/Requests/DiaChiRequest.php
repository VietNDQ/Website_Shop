<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiaChiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'so_dien_thoai'    => [
                'required',
                'string',
                'regex:/^[0-9]+$/',
                function ($attribute, $value, $fail) {
                    if (str_starts_with($value, '84')) {
                        if (strlen($value) !== 11) {
                            $fail('Số điện thoại bắt đầu bằng 84 phải có đúng 11 chữ số.');
                        }
                    } elseif (str_starts_with($value, '0')) {
                        if (strlen($value) !== 10) {
                            $fail('Số điện thoại bắt đầu bằng 0 phải có đúng 10 chữ số.');
                        }
                    } else {
                        $fail('Số điện thoại phải bắt đầu bằng 0 hoặc 84.');
                    }
                }
            ],
            'dia_chi_chi_tiet' => 'required|string|max:255',
            'thanh_pho'        => 'required|string|max:100',
            'quan_huyen'       => 'required|string|max:100',
            'thanh_pho_id'     => 'nullable|integer',
            'quan_huyen_id'    => 'nullable|integer',
            'phuong_xa_id'     => 'nullable|integer',
            'phuong_xa'        => 'nullable|string|max:100',
            'la_mac_dinh'      => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'so_dien_thoai.required'    => 'Số điện thoại không được để trống.',
            'so_dien_thoai.string'      => 'Số điện thoại không hợp lệ.',
            'so_dien_thoai.max'         => 'Số điện thoại không vượt quá 20 ký tự.',
            'so_dien_thoai.regex'       => 'Số điện thoại chỉ được chứa các chữ số.',
            
            'dia_chi_chi_tiet.required' => 'Địa chỉ chi tiết không được để trống.',
            'dia_chi_chi_tiet.string'   => 'Địa chỉ chi tiết phải là chuỗi ký tự.',
            'dia_chi_chi_tiet.max'      => 'Địa chỉ chi tiết không vượt quá 255 ký tự.',
            
            'thanh_pho.required'        => 'Thành phố/Tỉnh không được để trống.',
            'thanh_pho.string'          => 'Thành phố/Tỉnh không hợp lệ.',
            'thanh_pho.max'             => 'Thành phố/Tỉnh không vượt quá 100 ký tự.',
            
            'quan_huyen.required'       => 'Quận/Huyện không được để trống.',
            'quan_huyen.string'         => 'Quận/Huyện không hợp lệ.',
            'quan_huyen.max'            => 'Quận/Huyện không vượt quá 100 ký tự.',
            
            'la_mac_dinh.boolean'       => 'Trạng thái mặc định phải là true (đúng) hoặc false (sai).',
        ];
    }
}
