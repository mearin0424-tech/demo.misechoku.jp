<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'visible_to_cast' => ['sometimes', 'boolean'],
            'visible_to_shop' => ['sometimes', 'boolean'],
            'visible_to_guest' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'タイトル',
            'body' => '本文',
            'published_at' => '公開日時',
        ];
    }
}
