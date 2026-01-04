<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DateOfBirth implements Rule
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
        //
        // 年、月、日のいずれかが空であればエラー
        list($year, $month, $day) = $value;

        return !empty($year) && !empty($month) && !empty($day);

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '生年月日を選択してください。';
    }
}
