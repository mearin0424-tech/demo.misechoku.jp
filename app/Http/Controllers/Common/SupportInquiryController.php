<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\SupportInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SupportInquiryController extends Controller
{
    /**
     * サポート問い合わせフォーム送信処理
     *
     * 1) バリデーション（カテゴリ・メール・本文）
     * 2) support_inquiries テーブルに永続化
     * 3) 運営宛にログ出力（チャネル: stack）
     *    ※ メール / Slack 通知などは今後 .env 設定して追加可能
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', Rule::in(array_keys(SupportInquiry::CATEGORY_LABELS))],
            'email' => ['required', 'email:rfc', 'max:255'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'category.required' => 'お問い合わせ種別を選択してください。',
            'category.in' => '選択肢から選んでください。',
            'email.required' => '返信用メールアドレスを入力してください。',
            'email.email' => 'メールアドレスの形式が正しくありません。',
            'body.required' => 'お問い合わせ内容を入力してください。',
            'body.min' => 'お問い合わせ内容は 10 文字以上で入力してください。',
            'body.max' => 'お問い合わせ内容は 2000 文字以内で入力してください。',
        ]);

        [$senderType, $senderId] = $this->resolveSender($request);

        $inquiry = SupportInquiry::create([
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'category' => $validated['category'],
            'email' => $validated['email'],
            'body' => $validated['body'],
            'status' => SupportInquiry::STATUS_NEW,
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'ip_address' => $request->ip(),
        ]);

        Log::channel(config('logging.default', 'stack'))->info('サポート問い合わせを受け付けました', [
            'inquiry_id' => $inquiry->id,
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'category' => $validated['category'],
            'email' => $validated['email'],
        ]);

        return redirect()
            ->route('pages.support.form')
            ->with('support_inquiry_success', '問い合わせを受け付けました。返信まで数日いただく場合がございます。');
    }

    /**
     * 現在の送信元（cast / shop / guest）と ID を解決する
     *
     * @return array{0:string, 1:?string}
     */
    private function resolveSender(Request $request): array
    {
        if (Auth::guard('shop')->check()) {
            $shopUser = Auth::guard('shop')->user();
            return [SupportInquiry::SENDER_SHOP, (string) ($shopUser->shop_id ?? '')];
        }
        if (Auth::guard('member')->check()) {
            $castUser = Auth::guard('member')->user();
            return [SupportInquiry::SENDER_CAST, (string) ($castUser->id ?? '')];
        }
        return [SupportInquiry::SENDER_GUEST, null];
    }
}
