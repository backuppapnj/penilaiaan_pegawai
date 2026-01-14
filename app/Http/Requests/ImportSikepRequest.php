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
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020', 'max:'.(date('Y') + 1)],
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
            'month.required' => 'Bulan harus dipilih',
            'month.between' => 'Bulan tidak valid',
            'year.required' => 'Tahun harus dipilih',
            'year.min' => 'Tahun tidak valid',
            'year.max' => 'Tahun tidak valid',
        ];
    }
}
