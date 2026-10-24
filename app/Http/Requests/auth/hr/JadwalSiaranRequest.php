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
            'broadcast_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'notes' => ['nullable', 'string'],
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

    private function hasOverlappingSchedule(): bool
    {
        $currentSchedule = $this->route('jadwalSiaran');

        return BroadcastSchedule::query()
            ->where('user_id', $this->input('user_id'))
            ->whereDate('broadcast_date', $this->input('broadcast_date'))
            ->where('start_time', '<', $this->toFullTime($this->input('end_time')))
            ->where('end_time', '>', $this->toFullTime($this->input('start_time')))
            ->when($currentSchedule, fn ($query) => $query->whereKeyNot($currentSchedule->getKey()))
            ->exists();
    }

    private function toFullTime(string $time): string
    {
        return Carbon::createFromFormat('H:i', $time)->format('H:i:s');
    }
}
