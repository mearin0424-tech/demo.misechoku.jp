<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class KouzaMeig implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match('/^[ァ-ヴー・A-Z0-9]+$/u', (string) $value) === 1;


    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute はカタカナ・英大文字・数字で入力してください';
    }
}
