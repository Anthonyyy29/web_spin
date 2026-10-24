<?php

namespace App\Http\Requests\auth\hr;

use App\Http\Requests\auth\hr\Concerns\ValidatesCrewAssignment;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ProgramRequest extends FormRequest
{
    use ValidatesCrewAssignment;

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
            'name' => ['required', 'string', 'max:255'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'crewcall_time' => ['required', 'date_format:H:i'],
            'tapping_start' => ['required', 'date_format:H:i'],
            'tapping_end' => ['required', 'date_format:H:i'],
            'period_start' => ['required', 'date_format:Y-m-d'],
            'period_end' => ['required', 'date_format:Y-m-d', 'after_or_equal:period_start'],
            ...$this->crewRules(),
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
            'name' => 'nama program',
            'day_of_week' => 'hari',
            'crewcall_time' => 'jam crewcall',
            'tapping_start' => 'tapping mulai',
            'tapping_end' => 'tapping selesai',
            'period_start' => 'periode mulai',
            'period_end' => 'periode selesai',
            ...$this->crewAttributes(),
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
            'name.max' => ':Attribute maksimal :max karakter.',
            'integer' => ':Attribute harus berupa angka.',
            'day_of_week.between' => ':Attribute yang dipilih tidak valid.',
            'crewcall_time.date_format' => ':Attribute harus berformat jam:menit (H:i).',
            'tapping_start.date_format' => ':Attribute harus berformat jam:menit (H:i).',
            'tapping_end.date_format' => ':Attribute harus berformat jam:menit (H:i).',
            'period_start.date_format' => ':Attribute harus berformat tahun-bulan-tanggal (Y-m-d).',
            'period_end.date_format' => ':Attribute harus berformat tahun-bulan-tanggal (Y-m-d).',
            'period_end.after_or_equal' => ':Attribute harus sama dengan atau setelah periode mulai.',
            ...$this->crewMessages(),
        ];
    }

    /**
     * Get the "after" validation callables for the request.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->validateTimeOrder($validator);
                $this->validateCrewIsNotDuplicated($validator);
            },
        ];
    }

    /**
     * Get the validated program attributes with times stored as H:i:s.
     *
     * @return array<string, mixed>
     */
    public function programAttributes(): array
    {
        $validated = $this->safe()->only([
            'name',
            'day_of_week',
            'crewcall_time',
            'tapping_start',
            'tapping_end',
            'period_start',
            'period_end',
        ]);

        foreach (['crewcall_time', 'tapping_start', 'tapping_end'] as $field) {
            $validated[$field] = Carbon::createFromFormat('H:i', $validated[$field])->format('H:i:s');
        }

        return $validated;
    }

    /**
     * Require crewcall < tapping start < tapping end.
     */
    private function validateTimeOrder(Validator $validator): void
    {
        $errors = $validator->errors();
        $crewcallIsValid = ! $errors->has('crewcall_time');
        $tappingStartIsValid = ! $errors->has('tapping_start');
        $tappingEndIsValid = ! $errors->has('tapping_end');

        if ($crewcallIsValid && $tappingStartIsValid && $this->input('crewcall_time') >= $this->input('tapping_start')) {
            $errors->add('tapping_start', 'Tapping mulai harus setelah crewcall.');
        }

        if ($tappingStartIsValid && $tappingEndIsValid && $this->input('tapping_start') >= $this->input('tapping_end')) {
            $errors->add('tapping_end', 'Tapping selesai harus setelah tapping mulai.');
        }
    }
}
