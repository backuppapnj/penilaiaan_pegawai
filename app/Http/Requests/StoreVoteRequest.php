<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_id' => 'required|exists:periods,id',
            'employee_id' => 'required|exists:employees,id',
            'category_id' => 'required|exists:categories,id',
            'scores' => 'required|array|min:1',
            'scores.*.criterion_id' => 'required|exists:criteria,id',
            'scores.*.score' => 'required|numeric|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'period_id.required' => 'Periode wajib dipilih',
            'period_id.exists' => 'Periode tidak ditemukan',
            'employee_id.required' => 'Pegawai wajib dipilih',
            'employee_id.exists' => 'Pegawai tidak ditemukan',
            'category_id.required' => 'Kategori wajib dipilih',
            'category_id.exists' => 'Kategori tidak ditemukan',
            'scores.required' => 'Nilai wajib diisi',
            'scores.array' => 'Format nilai tidak valid',
            'scores.*.score.required' => 'Nilai wajib diisi',
            'scores.*.score.numeric' => 'Nilai harus berupa angka',
            'scores.*.score.min' => 'Nilai minimal 1',
            'scores.*.score.max' => 'Nilai maksimal 100',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $employeeId = $this->input('employee_id');
            $voterId = $this->user()?->employee?->id;

            if ($voterId && $employeeId == $voterId) {
                $validator->errors()->add('employee_id', 'Anda tidak dapat memberikan nilai untuk diri sendiri');
            }
        });
    }
}
