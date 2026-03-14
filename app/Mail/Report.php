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

class Report extends Mailable
{
    use Queueable, SerializesModels;

    private $shop_name;
    private $message;


    public function __construct( $member_id , $shop_name , $comment )
    {
        //
        $this->member_id = $member_id;
        $this->shop_name = $shop_name;
        $this->comment = $comment;
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
            ->to(\CommonConsts::MAIL_TO_SYSTEM)
            ->subject(\CommonConsts::STORE_REPORT_TITLE)
            ->view('shop.mails.report')
            ->with([
                'member_id' => $this->member_id,
                'shop_name' => $this->shop_name,
                'comment' => $this->comment,
            ]);

    }

}
