{{-- トーク開始 CTA（両カード共通）
     $talkRoute: string  ルート名
     $itemId: int        対象アカウントID --}}
<a href="{{ route($talkRoute, ['id' => $itemId, 'talk_topic' => 'other', 'initiate' => 1]) }}"
   class="swipe-talk-cta stop-propagation"
   aria-label="トークを開始する">
    <i class="fas fa-comment-dots" aria-hidden="true"></i> トークする
</a>
