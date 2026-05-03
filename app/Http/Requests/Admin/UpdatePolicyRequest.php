<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'lead_title' => ['nullable', 'string', 'max:200'],
            'lead_body' => ['nullable', 'string'],

            'meta' => ['nullable', 'array'],
            'meta.*.label' => ['nullable', 'string', 'max:120'],
            'meta.*.value' => ['nullable', 'string', 'max:1000'],

            'chapters' => ['nullable', 'array'],
            'chapters.*.title' => ['required_with:chapters.*.body', 'nullable', 'string', 'max:200'],
            'chapters.*.body' => ['required_with:chapters.*.title', 'nullable', 'string'],

            'updater_name' => ['required', 'string', 'max:120'],
            'change_summary' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'タイトル',
            'lead_title' => 'リード見出し',
            'lead_body' => 'リード本文',
            'updater_name' => '更新者名',
            'change_summary' => '更新内容メモ',
            'chapters.*.title' => '章タイトル',
            'chapters.*.body' => '章本文',
        ];
    }
}
