@php
    $shareUrl = $shareUrl ?? url()->current();
    $shareTitle = $shareTitle ?? trim($__env->yieldContent('title'));
    $shareText = $shareText ?? '';
    $shareLabel = $shareLabel ?? 'このページを共有';
    $xShareUrl = 'https://twitter.com/intent/tweet?url=' . rawurlencode($shareUrl) . '&text=' . rawurlencode(trim($shareTitle . ' ' . $shareText));
    $lineShareUrl = 'https://social-plugins.line.me/lineit/share?url=' . rawurlencode($shareUrl);
@endphp

@once
    @push('styles')
        <style>
            .share-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
                margin: 18px 0 28px;
            }

            .share-actions-label {
                width: 100%;
                margin: 0 0 4px;
                color: #f4e6d7;
                font-size: 0.9rem;
                letter-spacing: 0.08em;
            }

            .share-action-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 14px;
                border: 1px solid rgba(var(--accent-rgb, 214, 112, 162), 0.35);
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.04);
                color: #fff;
                font-size: 0.9rem;
                line-height: 1;
                text-decoration: none;
                transition: background-color 0.2s ease, transform 0.2s ease;
            }

            .share-action-btn:hover {
                background: rgba(var(--accent-rgb, 214, 112, 162), 0.12);
                transform: translateY(-1px);
            }

            .share-copy-status {
                font-size: 0.82rem;
                color: #eba8c8;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.share-actions').forEach(function (container) {
                var shareUrl = container.dataset.shareUrl || window.location.href;
                var shareTitle = container.dataset.shareTitle || document.title;
                var shareText = container.dataset.shareText || '';
                var nativeButton = container.querySelector('.js-native-share');
                var copyButton = container.querySelector('.js-copy-share');
                var status = container.querySelector('.share-copy-status');

                if (nativeButton) {
                    if (!navigator.share) {
                        nativeButton.hidden = true;
                    } else {
                        nativeButton.addEventListener('click', function () {
                            navigator.share({
                                title: shareTitle,
                                text: shareText,
                                url: shareUrl
                            }).catch(function () {});
                        });
                    }
                }

                if (copyButton) {
                    copyButton.addEventListener('click', async function () {
                        try {
                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                await navigator.clipboard.writeText(shareUrl);
                            } else {
                                var tempInput = document.createElement('input');
                                tempInput.value = shareUrl;
                                document.body.appendChild(tempInput);
                                tempInput.select();
                                document.execCommand('copy');
                                document.body.removeChild(tempInput);
                            }

                            if (status) {
                                status.textContent = 'URLをコピーしました';
                                window.setTimeout(function () {
                                    status.textContent = '';
                                }, 2500);
                            }
                        } catch (error) {
                            if (status) {
                                status.textContent = 'コピーに失敗しました';
                            }
                        }
                    });
                }
            });
        });
        </script>
    @endpush
@endonce

<div
    class="share-actions"
    data-share-url="{{ $shareUrl }}"
    data-share-title="{{ $shareTitle }}"
    data-share-text="{{ $shareText }}"
>
    <p class="share-actions-label">{{ $shareLabel }}</p>
    <button type="button" class="share-action-btn js-native-share">
        <i class="fas fa-share-alt"></i>
        <span>共有</span>
    </button>
    <a href="{{ $xShareUrl }}" target="_blank" rel="noopener noreferrer" class="share-action-btn">
        <i class="fab fa-twitter"></i>
        <span>X</span>
    </a>
    <a href="{{ $lineShareUrl }}" target="_blank" rel="noopener noreferrer" class="share-action-btn">
        <i class="fab fa-line"></i>
        <span>LINE</span>
    </a>
    <button type="button" class="share-action-btn js-copy-share">
        <i class="fas fa-link"></i>
        <span>URLをコピー</span>
    </button>
    <span class="share-copy-status" aria-live="polite"></span>
</div>
