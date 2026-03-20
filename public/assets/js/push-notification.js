/**
 * PWA Push 通知: 許可リクエスト・購読・アイコンバッジ・テスト送信
 */
(function () {
  function getCsrfToken() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function urlBase64ToUint8Array(base64String) {
    var padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    var rawData = atob(base64);
    var output = new Uint8Array(rawData.length);
    for (var i = 0; i < rawData.length; i++) {
      output[i] = rawData.charCodeAt(i);
    }
    return output;
  }

  function setAppBadge(count) {
    if (typeof navigator.setAppBadge !== 'function') return;
    if (count > 0) {
      navigator.setAppBadge(count).catch(function () {});
    } else {
      navigator.clearAppBadge().catch(function () {});
    }
  }

  function initBadgeFromPage() {
    var el = document.querySelector('[data-notification-badge]');
    var count = el ? parseInt(el.getAttribute('data-notification-badge'), 10) : 0;
    if (!isNaN(count)) {
      setAppBadge(count);
      if (navigator.serviceWorker && navigator.serviceWorker.controller) {
        navigator.serviceWorker.controller.postMessage({ type: 'badge-sync', count: count });
      }
    }
  }

  function enableNotifications() {
    if (!('Notification' in window) || !('serviceWorker' in navigator)) {
      alert('このブラウザは Push 通知に対応していません。');
      return Promise.resolve();
    }
    if (Notification.permission === 'granted') {
      return subscribeAndSend();
    }
    if (Notification.permission === 'denied') {
      alert('通知がブロックされています。ブラウザ設定で許可してください。');
      return Promise.resolve();
    }
    return Notification.requestPermission().then(function (perm) {
      if (perm !== 'granted') {
        alert('通知が許可されませんでした。');
        return;
      }
      return subscribeAndSend();
    });
  }

  function subscribeAndSend() {
    return fetch('/api/push/vapid-public-key')
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.error || !data.publicKey) {
          throw new Error(data.error || 'VAPID 未設定');
        }
        return navigator.serviceWorker.ready.then(function (reg) {
          return reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(data.publicKey),
          });
        });
      })
      .then(function (subscription) {
        var body = {
          endpoint: subscription.endpoint,
          keys: {
            p256dh: btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('p256dh')))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, ''),
            auth: btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('auth')))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, ''),
          },
        };
        return fetch('/api/push/subscribe', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify(body),
        });
      })
      .then(function (r) {
        if (!r.ok) throw new Error('購読の保存に失敗しました');
        return r.json();
      })
      .then(function () {
        if (typeof window.showPushEnabled === 'function') window.showPushEnabled();
        alert('通知を有効にしました。「テスト通知」で送信できます。');
      })
      .catch(function (err) {
        alert('エラー: ' + (err.message || '不明なエラー'));
      });
  }

  function sendTestNotification() {
    fetch('/api/push/send-test', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: '{}',
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.ok) {
          if (typeof window.showPushTestResult === 'function') {
            window.showPushTestResult(data.message);
          } else {
            alert(data.message);
          }
        } else {
          alert(data.message || '送信に失敗しました');
        }
      })
      .catch(function () {
        alert('リクエストに失敗しました');
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initBadgeFromPage();
    var btnInstall = document.getElementById('pwa-install-inline-btn');
    if (btnInstall && typeof window.triggerPwaInstall === 'function') {
      btnInstall.addEventListener('click', function () { window.triggerPwaInstall(); });
    }

    if (navigator.serviceWorker) {
      navigator.serviceWorker.addEventListener('message', function (event) {
        if (!event || !event.data || event.data.type !== 'badge-update') return;
        setAppBadge(Number(event.data.count || 0));
      });
    }

    document.querySelectorAll('#push-enable-btn').forEach(function (btnEnable) {
      btnEnable.addEventListener('click', function () { enableNotifications(); });
    });

    document.querySelectorAll('#push-test-btn').forEach(function (btnTest) {
      btnTest.addEventListener('click', function () { sendTestNotification(); });
    });
  });

  window.MisechokuPush = {
    enable: enableNotifications,
    sendTest: sendTestNotification,
    setBadge: setAppBadge,
  };
})();
