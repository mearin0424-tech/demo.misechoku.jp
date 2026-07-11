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
        // 8MB まで許容（スマホの高解像度撮影を許容）。クライアント側の image-editor.js が
        // 1MB 前後に再圧縮するため通常はこの上限には達しないが、フォールバック経路と
        // 未編集アップロードのために余裕を持たせる。
        // dimensions: 極端に巨大な画像はメモリ圧迫の温床なので上限を切る。
        return [
            'image' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:8192',
                'dimensions:max_width=6000,max_height=6000',
            ],
        ];
    }

    public function messages()
    {
        return [
            'image.required'   => '画像を選択してください。',
            'image.image'      => '画像ファイルを選択してください。',
            'image.mimes'      => 'JPEG/PNG/GIF/WebP 形式のみアップロードできます。iPhone の HEIC 形式は端末側で JPEG に変換してからお試しください。',
            'image.max'        => '画像は 8MB 以下のファイルをご利用ください。',
            'image.dimensions' => '画像サイズが大きすぎます。6000×6000 ピクセル以下のファイルをご利用ください。',
        ];
    }
}
