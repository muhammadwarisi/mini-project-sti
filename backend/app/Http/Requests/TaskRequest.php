<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'tenggat' => ['required', 'date'],
            'kelas' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul tugas wajib diisi.',
            'judul.string' => 'Judul tugas harus berupa teks.',
            'judul.max' => 'Judul tugas maksimal 255 karakter.',
            'deskripsi.string' => 'Deskripsi tugas harus berupa teks.',
            'tenggat.required' => 'Tenggat tugas wajib diisi.',
            'tenggat.date' => 'Format tenggat tugas tidak valid.',
            'kelas.required' => 'Kelas wajib diisi.',
            'kelas.string' => 'Kelas harus berupa teks.',
            'kelas.max' => 'Kelas maksimal 100 karakter.',
        ];
    }
}
