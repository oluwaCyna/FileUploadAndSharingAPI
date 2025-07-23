<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule', 'array<mixed>', 'string>
     */
    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'max:5'],
            'files.*' => ['file', 'max:102400', 'mimes:jpg,png,pdf,docx,zip'],
            'expires_in' => ['nullable', 'integer', 'min:1', 'max:30'],
            'email_to_notify' => ['nullable', 'email'],
            'password' => ['nullable', 'string', 'min:4'],
        ];
    }
}
