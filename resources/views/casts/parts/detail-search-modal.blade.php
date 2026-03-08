{{-- 詳細検索モーダル（賃貸検索風） --}}
<div id="detail-search-modal" class="detail-search-modal" aria-hidden="true">
    <div class="detail-search-modal__overlay" data-close-modal></div>
    <div class="detail-search-modal__window">
        <div class="detail-search-modal__header">
            <h2 class="detail-search-modal__title">詳細検索</h2>
            <button type="button" class="detail-search-modal__close" data-close-modal aria-label="閉じる">&times;</button>
        </div>
        <div class="detail-search-modal__body">
            <form id="detail-search-form" class="detail-search-form">
                {{-- 業種 --}}
                <div class="detail-search-accordion" data-accordion>
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>業種</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips">
                            @foreach(['キャバクラ', 'ラウンジ', 'バー', 'スナック', 'その他'] as $v)
                            <label class="detail-search-chip"><input type="checkbox" name="industry[]" value="{{ $v }}"><span>{{ $v }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- エリア --}}
                <div class="detail-search-row">
                    <label class="detail-search-label">エリア</label>
                    <button type="button" class="detail-search-select-btn" data-area-trigger>
                        <span class="detail-search-select-btn__text">選択する</span>
                        <span class="detail-search-select-btn__arrow">&gt;</span>
                    </button>
                </div>
                <div class="detail-search-row detail-search-row--radio">
                    <span class="detail-search-label">現在地・位置情報から探す</span>
                    <label class="detail-search-radio"><input type="radio" name="location_type" value="current" checked> 現在地から探す</label>
                    <label class="detail-search-radio"><input type="radio" name="location_type" value="geo"> 位置情報から探す</label>
                </div>

                {{-- 給与(時給) --}}
                <div class="detail-search-row">
                    <label class="detail-search-label">給与(時給)</label>
                    <button type="button" class="detail-search-select-btn" data-salary-trigger>
                        <span class="detail-search-select-btn__text">選択する</span>
                        <span class="detail-search-select-btn__chevron">&#9660;</span>
                    </button>
                </div>

                {{-- 採用報酬 --}}
                <div class="detail-search-row">
                    <label class="detail-search-label">採用報酬</label>
                    <button type="button" class="detail-search-select-btn" data-reward-trigger>
                        <span class="detail-search-select-btn__text">選択する</span>
                        <span class="detail-search-select-btn__chevron">&#9660;</span>
                    </button>
                </div>

                {{-- 給料の支払い方法 --}}
                <div class="detail-search-accordion" data-accordion>
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>給料の支払い方法</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips">
                            @foreach(['日払い', '週払い', '月払い', '即日払い'] as $v)
                            <label class="detail-search-chip"><input type="checkbox" name="payment[]" value="{{ $v }}"><span>{{ $v }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 働き方 --}}
                <div class="detail-search-accordion" data-accordion>
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>働き方</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips">
                            @foreach(['正社員', 'アルバイト', 'パート', '短期', '単発'] as $v)
                            <label class="detail-search-chip"><input type="checkbox" name="work_style[]" value="{{ $v }}"><span>{{ $v }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- お店の雰囲気 --}}
                <div class="detail-search-accordion" data-accordion>
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>お店の雰囲気</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips">
                            @foreach(['落ち着いた', 'にぎやか', '高級', 'カジュアル'] as $v)
                            <label class="detail-search-chip"><input type="checkbox" name="atmosphere[]" value="{{ $v }}"><span>{{ $v }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- メリット --}}
                <div class="detail-search-accordion" data-accordion>
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>メリット</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips">
                            @foreach(['ノルマなし', '送りあり', '寮あり', '未経験OK', '駅近'] as $v)
                            <label class="detail-search-chip"><input type="checkbox" name="merit[]" value="{{ $v }}"><span>{{ $v }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 特徴 --}}
                <div class="detail-search-accordion" data-accordion>
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>特徴</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips">
                            @foreach(['高時給', '体験入店可', 'Wワーク可', 'シフト自由'] as $v)
                            <label class="detail-search-chip"><input type="checkbox" name="feature[]" value="{{ $v }}"><span>{{ $v }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- 設備 --}}
                <div class="detail-search-accordion" data-accordion>
                    <button type="button" class="detail-search-accordion__head" data-accordion-trigger aria-expanded="false">
                        <span>設備</span>
                        <span class="detail-search-accordion__icon">+</span>
                    </button>
                    <div class="detail-search-accordion__body" hidden>
                        <div class="detail-search-chips">
                            @foreach(['個室', '駐車場', 'Wi-Fi', 'ドレス貸出'] as $v)
                            <label class="detail-search-chip"><input type="checkbox" name="facility[]" value="{{ $v }}"><span>{{ $v }}</span></label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="detail-search-modal__footer">
            <button type="button" class="detail-search-modal__btn detail-search-modal__btn--reset" data-detail-search-reset>条件をクリア</button>
            <button type="button" class="detail-search-modal__btn detail-search-modal__btn--submit" data-detail-search-submit>この条件で検索</button>
        </div>
    </div>
</div>
