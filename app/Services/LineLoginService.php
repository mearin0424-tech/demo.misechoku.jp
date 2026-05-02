<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineLoginService
{
    protected string $clientId;

    protected string $clientSecret;

    protected string $redirectUri;

    protected string $authorizeUrl = 'https://access.line.me/oauth2/v2.1/authorize';

    protected string $tokenUrl = 'https://api.line.me/oauth2/v2.1/token';

    protected string $profileUrl = 'https://api.line.me/v2/profile';

    public function __construct()
    {
        $this->clientId = config('services.line.client_id');
        $this->clientSecret = config('services.line.client_secret');
        $this->redirectUri = config('services.line.redirect');
    }

    /**
     * LINE認証画面へのURLを生成（state に role を入れてコールバックで識別）
     *
     * @param  string|null  $redirectUri  null のとき config の redirect（認可とトークン交換で同一文字列が必要）
     */
    public function getAuthorizationUrl(string $state, ?string $redirectUri = null): string
    {
        $redirectUri = $redirectUri ?? $this->redirectUri;
        // openid を付ける場合は nonce 必須（未指定だと LINE 側が 400 を返す）。/v2/profile は profile のみで可。
        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => 'profile',
        ]);

        return $this->authorizeUrl . '?' . $params;
    }

    /**
     * 認可コードをアクセストークンに交換
     *
     * @return array{access_token: string, id_token?: string, ...}
     */
    public function exchangeCode(string $code, ?string $redirectUri = null): array
    {
        $redirectUri = $redirectUri ?? $this->redirectUri;
        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (!$response->successful()) {
            Log::warning('LINE token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('LINE token exchange failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * アクセストークンでプロフィール取得（userId が LINE ユーザーID）
     *
     * @return array{userId: string, displayName?: string, pictureUrl?: string}
     */
    public function getProfile(string $accessToken): array
    {
        $response = Http::withToken($accessToken)->get($this->profileUrl);

        if (!$response->successful()) {
            throw new \RuntimeException('LINE profile fetch failed: ' . $response->body());
        }

        return $response->json();
    }
}
