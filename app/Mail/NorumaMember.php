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
use App\Models\Member;

class NorumaMember extends Mailable
{
    use Queueable, SerializesModels;
    private $user;
    private $member_id;
    private $name;


    public function __construct( $user,  $member_id, $name)
    {
        //
        $this->user = $user;
        $this->member_id = $member_id;
        $this->name = $name;

    }

    public function build()
    {

        //$tokenParam = ['reset_token' => $this->userToken->token];
        //$tokenParam = ['email_token' => $this->userToken];

        // $now = Carbon::now();

        // 48時間後を期限とした署名付きURLを生成
        // $url = URL::temporarySignedRoute('register.showForm', $now->addHours(\CommonConsts::PASSWORD_RESET_HOURS), $tokenParam);
        //$url = URL::signedRoute('register.showForm', $tokenParam);

        $url = route('shop.cast.index', ['member_id' => \StrUtil::enc($this->member_id)]);


        $cast_controller = app()->make('App\Http\Controllers\Shop\CastController');
        $shortUrl = $cast_controller -> createShortUrl($url) ;

        // ログに情報を出力
        //Log::info('Sending verification email', [
        //    'to' => $this->user,
        //    'subject' => \CommonConsts::NORUMA_MEMBER_TITLE,
        //    'url' => $url,
        //]);

        return $this->from(\CommonConsts::MAIL_FROM, config('app.name'))
            ->to($this->user)
            ->subject(\CommonConsts::NORUMA_MEMBER_TITLE)
            ->view('shop.mails.noruma_member')
            ->with([
                'url' => $shortUrl,
                'name' => $this->name,

            ]);

    }

}
