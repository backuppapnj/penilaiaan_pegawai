<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportSikepRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'], // Max 10MB
            'period_id' => ['required', 'integer', 'exists:periods,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'excel_file.required' => 'File Excel harus diunggah',
            'excel_file.mimes' => 'File harus berformat Excel (.xlsx atau .xls)',
            'excel_file.max' => 'Ukuran file maksimal 10MB',
            'period_id.required' => 'Periode harus dipilih',
            'period_id.exists' => 'Periode tidak valid',
        ];
    }
}
