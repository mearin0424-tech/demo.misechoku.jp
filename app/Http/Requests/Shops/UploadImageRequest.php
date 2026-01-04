<?php

namespace App\Http\Requests\Shops;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
{
    public function authorize()
    {
        return true; // 必要に応じて権限チェックを実装
    }

    public function rules()
    {
        return [
            'image' => [
                'required',
                'image',                   // 画像ファイルであること
                'mimes:jpeg,jpg,png,gif,webp', // 許可する拡張子
                'max:2048',                // サイズ制限 (2MB)
            ],
        ];
    }

    public function messages()
    {
        return [
            'image.image' => '画像ファイルを選択してください。',
            'image.mimes' => '許可されていないファイル形式です。',
            'image.max'   => 'ファイルサイズは2MB以内でアップロードしてください。',
        ];
    }
}