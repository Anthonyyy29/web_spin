<?php

namespace App\Http\Requests\auth\hr\Concerns;

use Illuminate\Validation\Validator;

/**
 * Shared validation for requests that carry crewcall, tapping start and tapping end times (H:i).
 */
trait ValidatesTimeOrder
{
    /**
     * Require crewcall < tapping start < tapping end.
     */
    protected function validateTimeOrder(Validator $validator): void
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
