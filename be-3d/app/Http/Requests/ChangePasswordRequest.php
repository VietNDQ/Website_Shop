<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('confirm_password') && !$this->has('new_password_confirmation')) {
            $this->merge([
                'new_password_confirmation' => $this->confirm_password,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|max:50|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Mật khẩu hiện tại không được để trống.',
            'current_password.string'   => 'Mật khẩu hiện tại không hợp lệ.',
            
            'new_password.required'     => 'Mật khẩu mới không được để trống.',
            'new_password.string'       => 'Mật khẩu mới phải là chuỗi ký tự.',
            'new_password.min'          => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'new_password.max'          => 'Mật khẩu mới không được vượt quá 50 ký tự.',
            'new_password.confirmed'    => 'Mật khẩu xác nhận không trùng khớp.',
        ];
    }
}
