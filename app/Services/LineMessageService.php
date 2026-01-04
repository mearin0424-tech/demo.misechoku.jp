<?php
 

namespace App\Services;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class LineMessageService
{
    protected $bot;

    public function __construct()
    {
        $this->bot = new LINEBot(
            new CurlHTTPClient(config('services.line.message.channel_token')),
            ['channelSecret' => config('services.line.message.channel_secret')]
        );
    }

    public function sendText($lineUserId, $message)
    {
        $textMessageBuilder = new TextMessageBuilder($message);
        return $this->bot->pushMessage($lineUserId, $textMessageBuilder);
    }
}
