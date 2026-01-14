<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCriterionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|required|exists:categories,id',
            'nama' => 'sometimes|required|string|max:255',
            'bobot' => 'sometimes|required|numeric|min:0|max:100',
            'urutan' => 'sometimes|required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih',
            'category_id.exists' => 'Kategori tidak ditemukan',
            'nama.required' => 'Nama kriteria wajib diisi',
            'bobot.required' => 'Bobot wajib diisi',
            'bobot.numeric' => 'Bobot harus berupa angka',
            'bobot.min' => 'Bobot minimal 0',
            'bobot.max' => 'Bobot maksimal 100',
            'urutan.required' => 'Urutan wajib diisi',
            'urutan.integer' => 'Urutan harus berupa angka',
        ];
    }
}
