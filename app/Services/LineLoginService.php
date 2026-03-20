<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
     */
    public function getAuthorizationUrl(string $state): string
    {
        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'state' => $state,
            'scope' => 'profile openid',
        ]);

        return $this->authorizeUrl . '?' . $params;
    }

    /**
     * 認可コードをアクセストークンに交換
     *
     * @return array{access_token: string, id_token?: string, ...}
     */
    public function exchangeCode(string $code): array
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (!$response->successful()) {
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
