// --- 1. 定義 ---
const REPO_PATH = './'; // ★相対パスに修正

// (Firebase設定等は一旦そのまま。必要なければ削除でもOK)
const firebaseConfig = {
  apiKey: "DUMMY", // ダミー
  authDomain: "dummy.firebaseapp.com",
  projectId: "dummy",
  storageBucket: "dummy.app",
  messagingSenderId: "000000000000",
  appId: "1:000000000000:web:dummy",
  measurementId: "G-DUMMY"
};

// 今回はPWA/通知機能はモックでは動作しないため、中身を空にしてエラーを防ぎます。
// 本格的に実装する場合は、Firebaseの設定が必要です。
console.log("PWA Loader Initialized (Mock Mode)");