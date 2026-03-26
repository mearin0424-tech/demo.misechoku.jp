<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoticeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('slug') === '') {
            $this->merge(['slug' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:notices,slug'],
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
            'slug' => 'スラッグ',
            'body' => '本文',
            'published_at' => '公開日時',
        ];
    }
}
