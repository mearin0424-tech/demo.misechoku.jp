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


class MailApprovalOff extends Mailable
{
    use Queueable, SerializesModels;
    private $user;
    private $message;


    public function __construct( $user,  $message)
    {
        //
        $this->user = $user;
        $this->message = $message;
    }

    public function build()
    {

        $url = route('register.showForm', ['email_token' => $this->userToken]);
        return $this->from(\CommonConsts::MAIL_FROM, config('app.name'))
            ->to($this->user)
            ->subject(\CommonConsts::MAIL_APPROVAL_OFF)
            ->view('shop.mails.mail_approval_off')
            ->with([
                'message' => $this->message,
            ]);
    }

}
