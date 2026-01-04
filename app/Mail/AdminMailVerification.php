<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
//use App\Models\ManagerToken;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;


class AdminMailVerification extends Mailable
{
    use Queueable, SerializesModels;
    private $user;
    private $userToken;


    public function __construct( $user,  $userToken)
    {
        //
        $this->user = $user;
        $this->userToken = $userToken;
    }

    public function build()
    {

        //$tokenParam = ['reset_token' => $this->userToken->token];
        $tokenParam = ['email_token' => $this->userToken];

        $now = Carbon::now();

        // 48時間後を期限とした署名付きURLを生成
        // $url = URL::temporarySignedRoute('register.showForm', $now->addHours(\CommonConsts::PASSWORD_RESET_HOURS), $tokenParam);
        //$url = URL::signedRoute('register.showForm', $tokenParam);
        $url = route('admin.register.showForm', $tokenParam);
        return $this->from(\CommonConsts::MAIL_FROM, config('app.name'))
            ->to($this->user)
            ->subject(\CommonConsts::MAIL_VERIFICATION_SUBJECT)
            ->view('admin.mails.mail_verification')
            ->with([
                'url' => $url,
            ]);

    }

}
