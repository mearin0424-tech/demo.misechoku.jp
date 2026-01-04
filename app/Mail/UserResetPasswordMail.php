<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use App\Models\Manager;
use App\Models\ManagerToken;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class UserResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
    private $user;
    private $userToken;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Manager $user, ManagerToken $userToken)
    {
        //
        $this->user = $user;
        $this->userToken = $userToken;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
/*
    public function envelope()
    {
        return new Envelope(
            subject: 'User Reset Password Mail',
        );
    }
*/
    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
/*
    public function content()
    {
        return new Content(
            view: 'view.name',
        );
    }
*/
    /**
     * Get the attachments for the message.
     *
     * @return array
     */
/*
    public function attachments()
    {
        return [];
    }
*/
    public function build()
    {
        $tokenParam = ['reset_token' => $this->userToken->token];
        $now = Carbon::now();

        // 48時間後を期限とした署名付きURLを生成
        $url = URL::temporarySignedRoute('password_reset.edit', $now->addHours(\CommonConsts::PASSWORD_RESET_HOURS), $tokenParam);

        return $this->from(\CommonConsts::MAIL_FROM, config('app.name'))
            ->to($this->user->email)
            ->subject('パスワード再設定')
            ->view('shop.mails.password_reset_mail')
            ->with([
                'user' => $this->user,
                'url' => $url,
            ]);
    }
}
