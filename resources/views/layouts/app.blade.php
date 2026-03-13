<!DOCTYPE html>
<html lang="ja">
<head>
    @php
        $pageTitle = trim($__env->yieldContent('title'));
        $metaTitle = trim($__env->yieldContent('meta_title'));
        $metaDescription = trim($__env->yieldContent('meta_description')) ?: 'ミセチョクのデモサイトです。';
        $metaImage = trim($__env->yieldContent('meta_image')) ?: asset('assets/images/pwa/icon-512.png');
        $canonicalUrl = trim($__env->yieldContent('canonical')) ?: url()->current();
        $assetVersion = '20260313-guide-2';
        $resolvedTitle = $metaTitle !== ''
            ? $metaTitle
            : ($pageTitle !== '' ? $pageTitle . ' | ' . config('app.name', 'ミセチョク') : config('app.name', 'ミセチョク'));
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:site_name" content="{{ config('app.name', 'ミセチョク') }}">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $resolvedTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $resolvedTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaImage }}">
    {{-- PWA --}}
    <meta name="theme-color" content="#190509">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ミセチョク">
    <link rel="manifest" href="{{ url('/manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/pwa/icon-192.png') }}">
    {{-- ファビコン（データURIで404防止。色はアプリのゴールド・ダーク） --}}
    <link rel="icon" href="data:image/svg+xml,{{ rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><rect width="32" height="32" fill="#190509"/><text x="16" y="22" font-size="18" text-anchor="middle" fill="#D4AF37">店</text></svg>') }}" type="image/svg+xml">
    <title>{{ $resolvedTitle }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Shippori+Mincho:wght@400;700&family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/layout-header.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/layout-footer.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/layout-sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/character-guide.css') }}?v={{ $assetVersion }}">

    <script src="{{ asset('assets/js/app.js') }}" defer></script>

    @stack('styles')
</head>
    
<body class="@yield('body-class')" data-notification-badge="{{ isset($unreadNewsCount) ? (int) $unreadNewsCount : 0 }}">
    <div id="bg-layer"></div>
    <div id="menu-overlay" class="menu-overlay"></div>

    <div id="app">
        {{-- メインレイアウト部分を縦並びのFlexコンテナとして包む --}}
        <div class="main-layout-container flex-1 flex flex-col min-w-0">

            {{-- ヘッダー --}}
            @include('layouts.parts.header')

            {{-- @yield('guide_message') で各ページの設定内容を注入する --}}
            @include('layouts.parts.character-guide', ['guideMessage' => $__env->yieldContent('guide_message')])

            {{-- メインコンテンツ（プロフィール詳細は content-wrapper を使わず幅をアプリ全体に統一） --}}
            <main id="main-content">
                @if(request()->routeIs('cast.shopprofileview.show', 'shop.castprofileview.show', 'cast.mypage.index', 'share.cast.show', 'share.recruit.show'))
                    @yield('content')
                @else
                    <div class="content-wrapper animate-fadeIn">
                        @yield('content')
                    </div>
                @endif
            </main>

            {{-- ボトムナビ（モバイル用固定）※店舗 / キャスト画面のみ表示 --}}
            @if (request()->is('shop/*') || request()->is('cast/*'))
                @include('layouts.parts.footer')
            @endif
        </div>
        {{-- サイドバー --}}
        @include('layouts.parts.sidebar')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="{{ asset('assets/js/character-guide.js') }}?v={{ $assetVersion }}"></script>
    <script src="{{ asset('assets/js/push-notification.js') }}"></script>

    {{-- 画像フルスクリーン用ライトボックス（全画面共通） --}}
    <div id="global-lightbox-overlay" class="lightbox-overlay" onclick="window._closeGlobalLightbox && window._closeGlobalLightbox(event)">
        <img id="global-lightbox-image" src="" alt="" class="lightbox-image">
        <button type="button" class="lightbox-close" aria-label="閉じる" onclick="window._closeGlobalLightbox && window._closeGlobalLightbox(event)">
            <i class="fas fa-times"></i>
        </button>
    </div>

    @stack('scripts')
    <script>
    (function () {
        function formatPostalCode(value) {
            var digits = String(value || '').replace(/\D+/g, '').slice(0, 7);

            if (digits.length <= 3) {
                return digits;
            }

            return digits.slice(0, 3) + '-' + digits.slice(3);
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[data-postal-code]').forEach(function (input) {
                var sync = function () {
                    input.value = formatPostalCode(input.value);
                };

                input.addEventListener('input', sync);
                input.addEventListener('blur', sync);
                sync();
            });
        });
    })();
    </script>
    <script>
    (function () {
        function normalizeAccountNumber(value) {
            return String(value || '').replace(/\D+/g, '').slice(0, 7);
        }

        function hiraganaToKatakana(value) {
            return String(value || '').replace(/[ぁ-ゖ]/g, function (char) {
                return String.fromCharCode(char.charCodeAt(0) + 0x60);
            });
        }

        function normalizeAccountName(value) {
            var normalized = String(value || '')
                .normalize('NFKC')
                .replace(/[\r\n]/g, '')
                .replace(/[ 　]/g, '')
                .toUpperCase();

            return hiraganaToKatakana(normalized);
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-account-number-input]').forEach(function (input) {
                var syncNumber = function () {
                    input.value = normalizeAccountNumber(input.value);
                };

                input.addEventListener('input', syncNumber);
                input.addEventListener('blur', syncNumber);
                syncNumber();
            });

            document.querySelectorAll('[data-account-name-input]').forEach(function (input) {
                var syncName = function () {
                    input.value = normalizeAccountName(input.value);
                };

                input.addEventListener('input', syncName);
                input.addEventListener('blur', syncName);
                syncName();
            });
        });
    })();
    </script>
    <script>
    (function () {
        function debounce(fn, wait) {
            var timer = null;

            return function () {
                var args = arguments;
                var context = this;
                clearTimeout(timer);
                timer = window.setTimeout(function () {
                    fn.apply(context, args);
                }, wait);
            };
        }

        function escapeHtml(value) {
            return String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function setOptions(listEl, items, formatLabel) {
            if (!listEl) {
                return;
            }

            listEl.innerHTML = items.map(function (item) {
                var label = formatLabel ? formatLabel(item) : '';

                return '<option value="' + escapeHtml(item.name) + '" label="' + escapeHtml(label) + '"></option>';
            }).join('');
        }

        function normalize(value) {
            return String(value || '').trim();
        }

        function fetchJson(url) {
            return fetch(url, {
                headers: {
                    Accept: 'application/json'
                }
            }).then(function (response) {
                if (!response.ok) {
                    throw new Error('Request failed');
                }

                return response.json();
            });
        }

        function mapBankItem(item) {
            return {
                code: String(item.code || ''),
                name: String(item.name || (item.normalize && item.normalize.name) || ''),
                short_name: String(item.short_name || item.name || ''),
                kana: String(item.kana || (item.normalize && item.normalize.kana) || ''),
                hira: String(item.hira || (item.normalize && item.normalize.hira) || '')
            };
        }

        function mapBranchItem(item, bankCode) {
            return {
                bank_code: String(item.bank_code || bankCode || ''),
                code: String(item.code || ''),
                name: String(item.name || (item.normalize && item.normalize.name) || ''),
                short_name: String(item.short_name || item.name || ''),
                kana: String(item.kana || (item.normalize && item.normalize.kana) || ''),
                hira: String(item.hira || (item.normalize && item.normalize.hira) || '')
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form[data-bank-autocomplete]').forEach(function (form) {
                var bankInput = form.querySelector('[data-bank-name-input]');
                var bankCodeInput = form.querySelector('[data-bank-code-input]');
                var bankList = form.querySelector('[data-bank-list]');
                var branchInput = form.querySelector('[data-branch-name-input]');
                var branchCodeInput = form.querySelector('[data-branch-code-input]');
                var branchList = form.querySelector('[data-branch-list]');

                if (!bankInput || !bankCodeInput || !bankList || !branchInput || !branchCodeInput || !branchList) {
                    return;
                }

                var bankMap = new Map();
                var branchCache = new Map();
                var branchMap = new Map();

                function syncSelectedBank() {
                    var key = normalize(bankInput.value);
                    var selected = bankMap.get(key);

                    bankCodeInput.value = selected ? selected.code : '';
                    branchCodeInput.value = '';

                    if (!selected) {
                        branchMap.clear();
                        branchList.innerHTML = '';
                    }
                }

                function syncSelectedBranch() {
                    var key = normalize(branchInput.value);
                    var selected = branchMap.get(key);

                    branchCodeInput.value = selected ? selected.code : '';
                }

                var searchBanks = debounce(function () {
                    var query = normalize(bankInput.value);

                    if (query.length < 1) {
                        bankMap.clear();
                        bankList.innerHTML = '';
                        bankCodeInput.value = '';
                        return;
                    }

                    fetchJson('/api/bank-lookup/banks?q=' + encodeURIComponent(query))
                        .then(function (data) {
                            var items = Array.isArray(data.items) ? data.items : [];
                            bankMap.clear();
                            items.forEach(function (item) {
                                bankMap.set(normalize(item.name), item);
                            });
                            setOptions(bankList, items, function (item) {
                                return item.code;
                            });
                            syncSelectedBank();

                            if (normalize(branchInput.value) !== '' && bankCodeInput.value) {
                                searchBranches();
                            }
                        })
                        .catch(function () {
                            return fetchJson('https://bank.teraren.com/banks.json').then(function (items) {
                                return Array.isArray(items) ? items.map(mapBankItem) : [];
                            });
                        })
                        .then(function (fallbackItems) {
                            if (!Array.isArray(fallbackItems)) {
                                return;
                            }

                            var filtered = fallbackItems.filter(function (item) {
                                var needle = query.toLowerCase();

                                return [item.code, item.name, item.short_name, item.kana, item.hira].some(function (value) {
                                    return normalize(value).toLowerCase().indexOf(needle) !== -1;
                                });
                            }).slice(0, 20);

                            bankMap.clear();
                            filtered.forEach(function (item) {
                                bankMap.set(normalize(item.name), item);
                            });
                            setOptions(bankList, filtered, function (item) {
                                return item.code;
                            });
                            syncSelectedBank();

                            if (normalize(branchInput.value) !== '' && bankCodeInput.value) {
                                searchBranches();
                            }
                        })
                        .catch(function () {
                            bankMap.clear();
                            bankList.innerHTML = '';
                        });
                }, 250);

                function loadBranches(bankCode) {
                    if (!bankCode) {
                        return Promise.resolve([]);
                    }

                    if (branchCache.has(bankCode)) {
                        return Promise.resolve(branchCache.get(bankCode));
                    }

                    return fetchJson('/api/bank-lookup/branches?bank_code=' + encodeURIComponent(bankCode))
                        .then(function (data) {
                            var items = Array.isArray(data.items) ? data.items : [];
                            branchCache.set(bankCode, items);

                            return items;
                        })
                        .catch(function () {
                            return fetchJson('https://bank.teraren.com/banks/' + encodeURIComponent(bankCode) + '/branches.json')
                                .then(function (items) {
                                    var mapped = Array.isArray(items)
                                        ? items.map(function (item) { return mapBranchItem(item, bankCode); })
                                        : [];

                                    branchCache.set(bankCode, mapped);

                                    return mapped;
                                });
                        });
                }

                var searchBranches = debounce(function () {
                    var bankCode = normalize(bankCodeInput.value);
                    var query = normalize(branchInput.value);

                    if (!bankCode) {
                        branchMap.clear();
                        branchList.innerHTML = '';
                        branchCodeInput.value = '';
                        return;
                    }

                    loadBranches(bankCode)
                        .then(function (items) {
                            var needle = query.toLowerCase();
                            var filtered = items.filter(function (item) {
                                if (query === '') {
                                    return true;
                                }

                                return [item.code, item.name, item.short_name, item.kana, item.hira].some(function (value) {
                                    return normalize(value).toLowerCase().indexOf(needle) !== -1;
                                });
                            }).slice(0, 30);

                            branchMap.clear();
                            filtered.forEach(function (item) {
                                branchMap.set(normalize(item.name), item);
                            });
                            setOptions(branchList, filtered, function (item) {
                                return item.code;
                            });
                            syncSelectedBranch();
                        })
                        .catch(function () {
                            branchMap.clear();
                            branchList.innerHTML = '';
                        });
                }, 250);

                bankInput.addEventListener('input', function () {
                    bankCodeInput.value = '';
                    branchInput.value = '';
                    branchCodeInput.value = '';
                    branchMap.clear();
                    branchList.innerHTML = '';
                    searchBanks();
                });

                bankInput.addEventListener('change', syncSelectedBank);
                bankInput.addEventListener('blur', syncSelectedBank);

                branchInput.addEventListener('focus', searchBranches);
                branchInput.addEventListener('input', function () {
                    branchCodeInput.value = '';
                    searchBranches();
                });
                branchInput.addEventListener('change', syncSelectedBranch);
                branchInput.addEventListener('blur', syncSelectedBranch);

                if (normalize(bankInput.value) !== '') {
                    searchBanks();
                }
            });
        });
    })();
    </script>
    <script>
    (function () {
        var overlay = document.getElementById('global-lightbox-overlay');
        var img = document.getElementById('global-lightbox-image');
        if (!overlay || !img) return;

        window.openImageLightbox = function (src) {
            if (!src) return;
            img.src = src;
            overlay.classList.add('is-open');
        };

        window._closeGlobalLightbox = function (e) {
            if (e) {
                if (e.target && !e.target.classList.contains('lightbox-overlay') && !e.target.closest('.lightbox-close')) {
                    return;
                }
                e.stopPropagation();
            }
            overlay.classList.remove('is-open');
            img.src = '';
        };

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.js-lightbox-target').forEach(function (el) {
                el.style.cursor = 'zoom-in';
                el.addEventListener('click', function () {
                    window.openImageLightbox(el.currentSrc || el.src);
                });
            });
        });
    })();
    </script>
    {{-- PWA: Service Worker 登録 --}}
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
          navigator.serviceWorker.register('{{ asset("sw.js") }}?v={{ $assetVersion }}', { scope: '/' })
            .then(function (reg) { /* 登録完了 */ })
            .catch(function () { /* 登録失敗時は無視 */ });
        });
      }
    </script>
    {{-- PWA: 手動インストール（スマホでインストールマークが出ない場合用） --}}
    <script>
      (function() {
        var deferredPrompt;
        var section = document.getElementById('pwa-install-section');
        var btn = document.getElementById('pwa-install-btn');
        if (!section || !btn) return;

        window.addEventListener('beforeinstallprompt', function(e) {
          e.preventDefault();
          deferredPrompt = e;
          section.style.display = 'block';
        });

        window.addEventListener('appinstalled', function() {
          deferredPrompt = null;
          if (section) section.style.display = 'none';
        });

        btn.addEventListener('click', function() {
          if (!deferredPrompt) return;
          deferredPrompt.prompt();
          deferredPrompt.userChoice.then(function(choice) {
            if (choice.outcome === 'accepted') {
              if (section) section.style.display = 'none';
            }
            deferredPrompt = null;
          });
        });

        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
          section.style.display = 'none';
        }
      })();
    </script>
</body>
</html>