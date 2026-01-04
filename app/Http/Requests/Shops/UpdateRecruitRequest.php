<?php

namespace App\Http\Requests\Shops;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecruitRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'catch_copy' => 'required|string|max:100',
            'message'    => 'required|string|max:1000',
            'wage'       => 'required|integer|min:0',
            'trial_wage' => 'nullable|integer|min:0',
            'salary_text' => 'nullable|string',
            'working_hours' => 'required|string',
            'working_days'  => 'required|string',
            'qualification' => 'required|string',
        ];
    }
}