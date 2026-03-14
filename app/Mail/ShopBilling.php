<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class ShopBilling extends Mailable
{
    use Queueable, SerializesModels;
    private $user;
    private $userToken;


    public function __construct( $user )
    {
        //
        $this->user = $user;
    }

    public function build()
    {

        //$tokenParam = ['reset_token' => $this->userToken->token];
        //$tokenParam = ['email_token' => $this->userToken];

        $now = Carbon::now();

        // 48時間後を期限とした署名付きURLを生成
        // $url = URL::temporarySignedRoute('register.showForm', $now->addHours(\CommonConsts::PASSWORD_RESET_HOURS), $tokenParam);
        //$url = URL::signedRoute('register.showForm', $tokenParam);
        //$url = route('register.showForm', ['email_token' => $this->userToken]);

        // ログに情報を出力
        //Log::info('Sending verification email', [
        //    'to' => $this->user,
        //    'subject' => \CommonConsts::FIRST_REGIST,
        //    'url' => $url,
        //]);

        return $this->from(\CommonConsts::MAIL_FROM, config('app.name'))
            ->to($this->user)
            ->subject(\CommonConsts::STORE_BILLING_TITLE)
            ->view('shop.mails.store_billing');
            //->with([
            //    'url' => $url,
            //]);

    }

}
