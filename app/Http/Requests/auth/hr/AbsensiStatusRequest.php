<?php

namespace App\Http\Requests\auth\hr;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AbsensiStatusRequest extends FormRequest
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
            'status' => ['required', 'in:izin,sakit,reset'],
            'note' => ['nullable', 'string', 'max:255'],
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
            'status' => 'status absensi',
            'note' => 'catatan',
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
            'required' => ':Attribute wajib dipilih.',
            'status.in' => ':Attribute harus izin, sakit, atau kembalikan.',
            'note.string' => ':Attribute harus berupa teks.',
            'note.max' => ':Attribute maksimal :max karakter.',
        ];
    }

    /**
     * Get the validated status and note; any submitted check-in time is ignored.
     *
     * @return array{status: string, note?: string|null}
     */
    public function statusChange(): array
    {
        return $this->safe()->only(['status', 'note']);
    }
}
