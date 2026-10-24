<?php

namespace App\Http\Requests\auth\hr\Concerns;

use App\Enums\CrewPosition;
use App\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Shared validation for requests that assign announcers and sound operators to a program or session.
 */
trait ValidatesCrewAssignment
{
    /**
     * Get the validation rules for the crew fields.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function crewRules(): array
    {
        return [
            'announcer_ids' => ['required', 'array', 'min:1'],
            'announcer_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->whereIn('role_id', $this->roleIdsFor(CrewPosition::Announcer)),
            ],
            'so_ids' => ['nullable', 'array'],
            'so_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->whereIn('role_id', $this->roleIdsFor(CrewPosition::So)),
            ],
        ];
    }

    /**
     * Get the attribute names for the crew fields.
     *
     * @return array<string, string>
     */
    protected function crewAttributes(): array
    {
        return [
            'announcer_ids' => 'announcer',
            'announcer_ids.*' => 'announcer',
            'so_ids' => 'sound operator (SO)',
            'so_ids.*' => 'sound operator (SO)',
        ];
    }

    /**
     * Get the validation messages for the crew fields.
     *
     * @return array<string, string>
     */
    protected function crewMessages(): array
    {
        return [
            'announcer_ids.min' => 'Pilih minimal :min announcer.',
            'announcer_ids.array' => ':Attribute harus berupa daftar pilihan.',
            'so_ids.array' => ':Attribute harus berupa daftar pilihan.',
            'announcer_ids.*.exists' => ':Attribute yang dipilih tidak valid.',
            'so_ids.*.exists' => ':Attribute yang dipilih tidak valid.',
            'announcer_ids.*.integer' => ':Attribute yang dipilih tidak valid.',
            'so_ids.*.integer' => ':Attribute yang dipilih tidak valid.',
            'announcer_ids.*.distinct' => ':Attribute dipilih lebih dari sekali.',
            'so_ids.*.distinct' => ':Attribute dipilih lebih dari sekali.',
        ];
    }

    /**
     * Add an error when the same person is assigned to both positions.
     */
    protected function validateCrewIsNotDuplicated(Validator $validator): void
    {
        if ($validator->errors()->hasAny(['announcer_ids', 'announcer_ids.*', 'so_ids', 'so_ids.*'])) {
            return;
        }

        $duplicates = array_intersect($this->announcerIds(), $this->soIds());

        if ($duplicates !== []) {
            $validator->errors()->add('so_ids', 'Satu orang tidak boleh menjadi announcer dan sound operator (SO) sekaligus.');
        }
    }

    /**
     * Get the crew assignment as user id => position.
     *
     * @return array<int, string>
     */
    public function crewPositions(): array
    {
        $positions = [];

        foreach ($this->soIds() as $userId) {
            $positions[$userId] = CrewPosition::So->value;
        }

        foreach ($this->announcerIds() as $userId) {
            $positions[$userId] = CrewPosition::Announcer->value;
        }

        return $positions;
    }

    /**
     * Get the ids of the roles that may be assigned to the given position.
     *
     * @return list<int>
     */
    private function roleIdsFor(CrewPosition $position): array
    {
        return Role::whereIn('name', $position->roleNames())->pluck('id')->all();
    }

    /**
     * @return array<int, int>
     */
    private function announcerIds(): array
    {
        return array_map('intval', (array) $this->input('announcer_ids', []));
    }

    /**
     * @return array<int, int>
     */
    private function soIds(): array
    {
        return array_map('intval', (array) $this->input('so_ids', []));
    }
}
