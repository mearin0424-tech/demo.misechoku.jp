<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Manager;
//use App\Models\ManagerToken;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class MailVerification extends Mailable
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
        $now = Carbon::now();
        $url = route('register.showForm', ['email_token' => $this->userToken]);

        Log::info('Sending verification email', [
            'to' => $this->user,
            'subject' => \CommonConsts::FIRST_REGIST,
            'url' => $url,
        ]);

        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->to($this->user)
            ->subject(\CommonConsts::MAIL_VERIFICATION_SUBJECT)
            ->view('shop.mails.mail_verification')
            ->with(['url' => $url]);
    }


}
