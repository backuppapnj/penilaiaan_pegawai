<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'semester' => 'sometimes|required|in:ganjil,genap',
            'year' => 'sometimes|required|integer|min:2020|max:2100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'sometimes|required|in:draft,open,closed,announced',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama periode wajib diisi',
            'semester.required' => 'Semester wajib dipilih',
            'semester.in' => 'Semester harus ganjil atau genap',
            'year.required' => 'Tahun wajib diisi',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai',
        ];
    }
}
