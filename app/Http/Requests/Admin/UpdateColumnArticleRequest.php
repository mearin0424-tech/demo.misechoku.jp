<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateColumnArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('column_category_id') === '') {
            $this->merge(['column_category_id' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'column_category_id' => [
                'required',
                'integer',
                Rule::exists('column_categories', 'id')->where(function ($query) {
                    $query->where('del_flg', 0);
                }),
            ],
            'body' => ['required', 'string'],
            'tags' => ['nullable', 'string', 'max:300'],
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
            'column_category_id' => 'カテゴリ',
            'body' => '本文',
            'tags' => 'タグ',
            'published_at' => '公開日時',
        ];
    }
}
