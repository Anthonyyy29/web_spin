<?php

namespace App\Http\Requests\auth\hr;

use App\Enums\SessionStatus;
use App\Http\Requests\auth\hr\Concerns\ValidatesCrewAssignment;
use App\Http\Requests\auth\hr\Concerns\ValidatesTimeOrder;
use App\Models\BroadcastSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SesiRequest extends FormRequest
{
    use ValidatesCrewAssignment;
    use ValidatesTimeOrder;

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
            'session_date' => ['required', 'date_format:Y-m-d'],
            'crewcall_time' => ['required', 'date_format:H:i'],
            'tapping_start' => ['required', 'date_format:H:i'],
            'tapping_end' => ['required', 'date_format:H:i'],
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
            'session_date' => 'tanggal sesi',
            'crewcall_time' => 'jam crewcall',
            'tapping_start' => 'tapping mulai',
            'tapping_end' => 'tapping selesai',
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
            'session_date.date_format' => ':Attribute harus berformat tahun-bulan-tanggal (Y-m-d).',
            'crewcall_time.date_format' => ':Attribute harus berformat jam:menit (H:i).',
            'tapping_start.date_format' => ':Attribute harus berformat jam:menit (H:i).',
            'tapping_end.date_format' => ':Attribute harus berformat jam:menit (H:i).',
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
                $this->validateScheduleIsNotMovedAfterCheckIn($validator);
                $this->validateCrewIsNotDuplicated($validator);
                $this->validateCrewHasNoOverlap($validator);
            },
        ];
    }

    /**
     * Get the validated session attributes with times stored as H:i:s and the session scheduled.
     *
     * @return array<string, mixed>
     */
    public function sessionAttributes(): array
    {
        $validated = $this->safe()->only(['session_date', 'crewcall_time', 'tapping_start', 'tapping_end']);

        foreach (['crewcall_time', 'tapping_start', 'tapping_end'] as $field) {
            $validated[$field] = Carbon::createFromFormat('H:i', $validated[$field])->format('H:i:s');
        }

        return [
            ...$validated,
            'status' => SessionStatus::Scheduled,
            'is_customized' => true,
        ];
    }

    /**
     * Reject a new date or time for a session that already has a check-in recorded on the old schedule.
     */
    private function validateScheduleIsNotMovedAfterCheckIn(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        /** @var BroadcastSession $current */
        $current = $this->route('sesi');
        $stored = [
            'session_date' => $current->session_date?->toDateString(),
            'crewcall_time' => $current->crewcall_time,
            'tapping_start' => $current->tapping_start,
            'tapping_end' => $current->tapping_end,
        ];
        $submitted = [
            'session_date' => $this->input('session_date'),
            'crewcall_time' => Carbon::createFromFormat('H:i', $this->input('crewcall_time'))->format('H:i:s'),
            'tapping_start' => Carbon::createFromFormat('H:i', $this->input('tapping_start'))->format('H:i:s'),
            'tapping_end' => Carbon::createFromFormat('H:i', $this->input('tapping_end'))->format('H:i:s'),
        ];

        $isMoved = collect($stored)->contains(fn (?string $value, string $field): bool => $value !== $submitted[$field]);

        if ($isMoved && $current->hasCheckIns()) {
            $validator->errors()->add('session_date', BroadcastSession::LOCKED_SCHEDULE_MESSAGE);
        }
    }

    /**
     * Reject crew members already booked in another scheduled session whose tapping overlaps on the same date.
     */
    private function validateCrewHasNoOverlap(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $tappingStart = Carbon::createFromFormat('H:i', $this->input('tapping_start'))->format('H:i:s');
        $tappingEnd = Carbon::createFromFormat('H:i', $this->input('tapping_end'))->format('H:i:s');
        /** @var BroadcastSession $current */
        $current = $this->route('sesi');

        $bookedCrew = User::query()
            ->whereIn('id', array_keys($this->crewPositions()))
            ->whereHas('broadcastSessions', function ($query) use ($current, $tappingStart, $tappingEnd): void {
                $query->where('broadcast_sessions.status', SessionStatus::Scheduled)
                    ->whereDate('broadcast_sessions.session_date', $this->input('session_date'))
                    ->where('broadcast_sessions.id', '!=', $current->id)
                    ->where('broadcast_sessions.tapping_start', '<', $tappingEnd)
                    ->where('broadcast_sessions.tapping_end', '>', $tappingStart);
            })
            ->orderBy('username')
            ->get();

        foreach ($bookedCrew as $user) {
            $validator->errors()->add('tapping_start', "Bentrok dengan sesi lain milik {$user->username}.");
        }
    }
}
