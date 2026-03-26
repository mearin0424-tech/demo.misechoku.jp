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
        if ($this->input('slug') === '') {
            $this->merge(['slug' => null]);
        }
        if ($this->input('column_category_id') === '') {
            $this->merge(['column_category_id' => null]);
        }
    }

    public function rules(): array
    {
        $column = $this->route('column');

        return [
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable',
                'string',
                'max:191',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('column_articles', 'slug')->ignore($column),
            ],
            'column_category_id' => [
                'required',
                'integer',
                Rule::exists('column_categories', 'id')->where(function ($query) {
                    $query->where('del_flg', 0);
                }),
            ],
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
            'column_category_id' => 'カテゴリ',
            'body' => '本文',
            'published_at' => '公開日時',
        ];
    }
}
