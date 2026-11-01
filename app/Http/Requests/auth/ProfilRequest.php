<?php

namespace App\Http\Requests\auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfilRequest extends FormRequest
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
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($this->user())],
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'photo' => 'foto profil',
            'username' => 'username',
        ];
    }

    /**
     * Get custom validation messages in Indonesian.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'string' => ':Attribute harus berupa teks.',
            'username.max' => ':Attribute maksimal :max karakter.',
            'username.unique' => 'Username ini sudah dipakai.',
            'photo.image' => 'Foto profil harus berupa gambar JPG, PNG, atau WebP.',
            'photo.mimes' => 'Foto profil harus berupa gambar JPG, PNG, atau WebP.',
            'photo.max' => 'Foto profil maksimal 2 MB.',
            'photo.uploaded' => 'Foto profil gagal diunggah. Pastikan ukurannya maksimal 2 MB.',
        ];
    }
}
