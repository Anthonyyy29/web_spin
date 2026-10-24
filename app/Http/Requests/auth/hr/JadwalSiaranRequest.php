<?php

namespace App\Http\Requests\auth\hr;

use App\Models\BroadcastSchedule;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class JadwalSiaranRequest extends FormRequest
{
    /**
     * End time that marks the end of the broadcast day (midnight).
     */
    private const MIDNIGHT = '00:00';

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
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'announcer'),
            ],
            'program_title' => ['required', 'string', 'max:255'],
            'broadcast_date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => [
                'required',
                'date_format:H:i',
                Rule::when($this->input('end_time') !== self::MIDNIGHT, 'after:start_time'),
            ],
            'notes' => ['nullable', 'string'],
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
            'user_id' => 'announcer',
            'program_title' => 'judul program',
            'broadcast_date' => 'tanggal siaran',
            'start_time' => 'jam mulai',
            'end_time' => 'jam selesai',
            'notes' => 'catatan',
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
            'program_title.max' => ':Attribute maksimal :max karakter.',
            'user_id.exists' => ':Attribute yang dipilih tidak valid.',
            'broadcast_date.date_format' => ':Attribute harus berformat tahun-bulan-tanggal (Y-m-d).',
            'start_time.date_format' => ':Attribute harus berformat jam:menit (H:i).',
            'end_time.date_format' => ':Attribute harus berformat jam:menit (H:i).',
            'end_time.after' => ':Attribute harus lebih besar dari jam mulai, atau 00:00 untuk tengah malam.',
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
                if ($validator->errors()->hasAny(['user_id', 'broadcast_date', 'start_time', 'end_time'])) {
                    return;
                }

                if ($this->hasOverlappingSchedule()) {
                    $validator->errors()->add(
                        'start_time',
                        'Jadwal bentrok dengan jadwal lain milik announcer ini.'
                    );
                }
            },
        ];
    }

    /**
     * Get the validated schedule attributes with times stored as H:i:s.
     *
     * @return array<string, mixed>
     */
    public function scheduleAttributes(): array
    {
        $validated = $this->validated();

        $validated['start_time'] = $this->toFullTime($validated['start_time']);
        $validated['end_time'] = $this->toFullTime($validated['end_time']);

        return $validated;
    }

    /**
     * Determine if another schedule of the same announcer and date overlaps this one.
     *
     * An end time of 00:00 means midnight, so it is compared as 24:00:00.
     */
    private function hasOverlappingSchedule(): bool
    {
        $currentSchedule = $this->route('jadwalSiaran');
        $endTime = $this->input('end_time') === self::MIDNIGHT
            ? '24:00:00'
            : $this->toFullTime($this->input('end_time'));

        return BroadcastSchedule::query()
            ->where('user_id', $this->input('user_id'))
            ->whereDate('broadcast_date', $this->input('broadcast_date'))
            ->where('start_time', '<', $endTime)
            ->where(fn ($query) => $query
                ->where('end_time', '>', $this->toFullTime($this->input('start_time')))
                ->orWhere('end_time', $this->toFullTime(self::MIDNIGHT)))
            ->when($currentSchedule, fn ($query) => $query->whereKeyNot($currentSchedule->getKey()))
            ->exists();
    }

    private function toFullTime(string $time): string
    {
        return Carbon::createFromFormat('H:i', $time)->format('H:i:s');
    }
}
