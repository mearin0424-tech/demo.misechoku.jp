# DESIGN.md — デザインシステムの単一の正

このファイルは UI 実装の **唯一の正（Single Source of Truth）** です。
全画面はここに定義されたトークンと x-ui コンポーネントだけで組みます。
CLAUDE.md から本ファイルを参照させ、画面移行のたびに先頭に読み込ませてください。

実体は `resources/css/app.css` の `@theme` ブロックにあります。本ファイルは
「何を・なぜ・どう使うか」を定義する仕様書です。

---

## 0. 一本化の決定事項（2枚のプロトタイプの食い違いを解消）

| 項目 | プロトA（mypage） | プロトB（その他画面） | 採用 |
|---|---|---|---|
| **プライマリボタン色** | アクセント追従（`#8B5CF6` / グラデ `#A78BFA→#7C3AED`） | 固定ディープパープル（`#581C87` / グラデ `#6B21A8→#3B0764`） | **★要確認：アクセント追従を既定** |
| ボタン上の文字色 | `#E6DFFC` / グラデ `#F4F2FA` | `#F3E8FF` / グラデ `#FAF5FF` | アクセント追従（`--on-accent`） |
| アクセント枠 | `#A855F7/40` | `#A855F7/40` | 一致 → `border-line-accent/40` |
| 背景 / 文字 / サブ | `#050505 / #F5F5F5 / #A0A0A0` | 同左 | 一致 |

> **★確認してほしい1点：プライマリボタンの色**
> A はテーマ（アメジスト/ライラック/水色/ピンク）に追従、B はテーマに関係なく固定ディープパープル。
> テーマスイッチャーを活かす観点から **既定はアクセント追従** にしています。
> 固定ディープパープルにしたい場合は、ボタンの class を
> `bg-accent` → `bg-deep-purple-btn` / `from-accent-grad-from to-accent-grad-to` → `from-deep-purple-from to-deep-purple-to` に
> 差し替えるだけ（1行）です。

---

## 1. カラートークン

| 用途 | トークン（utility） | 値 |
|---|---|---|
| アプリ背景 | `bg-base` | `#050505` |
| 3Dカード上端グラデ | `from-surface-from` | `#1a1a1a`（→ `to-base`） |
| 本文 | `text-text-main` | `#F5F5F5` |
| サブ文字 | `text-text-sub` | `#A0A0A0` |
| 通常ボーダー | `border-line` | `#2A2A2A` |
| アクセント枠 | `border-line-accent/40` | `#A855F7` @40% |
| アクセント面 | `bg-accent` | テーマ追従 |
| アクセント文字 | `text-accent-text` | テーマ追従 |
| アクセントグラデ | `from-accent-grad-from to-accent-grad-to` | テーマ追従 |
| アクセント上の文字 | `text-on-accent` / `text-on-accent-strong` | テーマ追従 |
| ディープパープル | `bg-deep-purple` | `#9333EA` |
| ボーナス金グラデ | `from-gold-from to-gold-to` | `#D4AF37 → #B8860B` |
| 明るい入力背景 | `bg-input-light` | `#F5F2FA` |

**禁止：** `bg-[#050505]` のような任意値、`text-white` 等のベタ指定（白はアイコンのみ可）。

---

## 2. テーマ切替

ルート要素の `data-theme` を切り替える（`amethyst`（既定）/ `lilac` / `light_blue` / `light_pink`）。
アクセント系トークンはすべて CSS 変数経由なので、属性を変えるだけで全画面が一括追従する。
画面ごとに色分岐を書かない。

```html
<html data-theme="amethyst"> ... </html>
```

---

## 3. タイポグラフィ

| 用途 | utility |
|---|---|
| 本文 | `font-sans`（Noto Sans JP・既定） |
| 見出し・英字ロゴ | `.app-title`（Montserrat / letter-spacing 0.05em） |

---

## 4. シャドウ（3D / ニューモーフィズム）

| utility | 用途 |
|---|---|
| `shadow-btn-3d` / `shadow-btn-3d-active` | ボタン（押下時は `active:` で差し替え） |
| `shadow-badge-3d` | バッジ |
| `shadow-fab-3d` | FAB |
| `shadow-card-3d` | カード |
| `shadow-header` / `shadow-footer` | グラスヘッダー / フッター |
| `shadow-input-dark` / `shadow-input-light` | 入力欄 |
| `shadow-gold-3d` | ボーナス金バッジ |
| `shadow-nav-pill` | ナビ3Dスタイルのアクティブ丸 |

**禁止：** `shadow-[inset_0_4px_6px_...]` の任意値。上記トークンを使う。

---

## 5. アイコン辞書（Phosphor）

**生クラス（`ph-fill ph-house` 等）を画面に直書きしない。** 必ず意味名で `<x-ui.icon>` を呼ぶ。
新しいアイコンが要るときは、まずこの表に意味名を追加してから使う。

| 意味名（name） | Phosphor クラス |
|---|---|
| `home` | `ph-fill ph-house` |
| `search` | `ph-bold ph-magnifying-glass` |
| `likes` | `ph-fill ph-heart` |
| `talk` | `ph-fill ph-chat-teardrop-text` |
| `mypage` | `ph-fill ph-user` |
| `back` | `ph-bold ph-caret-left` |
| `share` | `ph-bold ph-share-network` |
| `like`（スワイプ） | `ph-fill ph-heart` |
| `nope`（スワイプ） | `ph-bold ph-x` |
| `super`（スワイプ） | `ph-fill ph-star` |
| `forward` | `ph-bold ph-caret-right` |
| `settings` | `ph-bold ph-gear-six` |
| `close` | `ph-bold ph-x` |
| `plus` | `ph-bold ph-plus` |
| `check` | `ph-bold ph-check` |
| `list` | `ph-bold ph-list`（ハンバーガー / メニュー） |
| `bell` | `ph-bold ph-bell`（通知） |
| `task` | `ph-fill ph-check-circle`（タスク） |

> 読み込みは npm パッケージ `@phosphor-icons/web` を `resources/js/app.js` で import する想定（CDN webfont 廃止）。

---

## 6. アニメーション / トランジション

| utility | 効果 |
|---|---|
| `animate-slide-up` | 下から30pxフェードイン（カード・モーダル登場） |
| `animate-fade-in` | scale 0.95→1 のフェードイン |
| `transition-all duration-300` | 既定のトランジション尺（ホバー・状態変化） |

keyframes はコンパイル済み CSS にのみ存在する。**画面側に `<style>` で keyframes を書かない。**

---

## 7. コンポーネント一覧（x-ui.*）

各画面はこれらを組み合わせるだけ。class レシピ（= 実装の根拠）を併記。
`is3D` / `isGrad` 等のバリエーションは props で出し分ける。

| コンポーネント | class レシピ（既定） |
|---|---|
| `x-ui.button`（primary） | `inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full font-bold bg-accent text-on-accent shadow-btn-3d active:translate-y-1.5 active:shadow-btn-3d-active transition-all duration-300` |
| `x-ui.button`（grad） | 上記の `bg-accent` を `bg-gradient-to-r from-accent-grad-from to-accent-grad-to text-on-accent-strong` に置換 |
| `x-ui.badge` | `px-4 py-2 rounded-full bg-accent text-on-accent shadow-badge-3d font-bold text-sm` |
| `x-ui.fab` | `fixed bottom-[90px] right-5 w-14 h-14 rounded-full flex items-center justify-center bg-accent text-on-accent shadow-fab-3d active:translate-y-1.5 transition-all z-30` |
| `x-ui.card` | `rounded-card overflow-hidden border border-line-accent/40 bg-gradient-to-br from-surface-from to-base shadow-card-3d transition-all duration-300`（flat 時は bg/shadow を外す） |
| `x-ui.menu-card` | card に `p-4 flex items-center justify-between rounded-panel cursor-pointer group` |
| `x-ui.header`（glass） | `bg-deep-purple/30 backdrop-blur-md border-b border-line-accent/40 shadow-header pt-safe` |
| `x-ui.bottom-nav` | `bg-deep-purple/30 backdrop-blur-md border-t border-line-accent/40 shadow-footer pb-safe`（中身は §8 参照） |
| `x-ui.icon` | `<i class="{Phosphorクラス}">`（name → §5 で解決） |
| `x-ui.input` | dark: `bg-accent/10 border border-line-accent/40 shadow-input-dark text-text-main` / light: `bg-input-light shadow-input-light text-input-light-text` |
| message bubble（自分） | `px-4 py-2.5 rounded-[20px] rounded-tr-sm bg-accent text-on-accent shadow-badge-3d font-medium text-[13px] leading-relaxed w-fit` |
| message bubble（相手） | `... rounded-tl-sm` + card と同じ面 |

---

## 8. インタラクション / アクション一覧（data属性駆動の共通JS）

挙動は画面ごとに書かず、**1つの behaviors.js が data 属性を見て付与する。**
画面の Blade は「属性を置くだけ」。これでアクションが全画面で完全に一致する。

| data 属性 | 挙動 |
|---|---|
| `data-swipe-deck` / `data-swipe-action="like\|nope\|super"` | カードのドラッグ＆スワイプ判定・アクション発火 |
| `data-bottom-nav` / `data-nav-style="neon\|flat\|3d"` | 下部ナビのアクティブ表示・スタイル出し分け |
| `data-fab` | FABの押下挙動 |
| `data-tabs` / `data-tab-panel="..."` | タブ切替（例：gallery / details） |
| `data-scroll-reveal` | スクロール量に応じたヘッダーの出し入れ（`isScrolled` 相当） |
| `data-message-form` | メッセージ送信・吹き出し追加 |

---

## 9. 画面移行ルール（Claude Code 向け）

**必ず守る：**
- 本ファイルのトークンと `x-ui.*` だけで組む
- アイコンは `<x-ui.icon name="...">`、アクションは data 属性
- 1画面ずつ。完了後に `npm run build` を通し、purge 警告ゼロを確認 → 目視確認 → 移行チェックリストを更新

**禁止：**
- 任意値（`bg-[#...]` `shadow-[...]` `text-[13px]` 等。`text-[13px]` のような一回限りのサイズは可だが色・影は不可）
- Phosphor 生クラスの直書き
- 画面内の `<style>` での keyframes / 色定義
- 文字列連結によるクラス生成（`'bg-' + x`）。purge で消える
- 画面ごとの色分岐（テーマは `data-theme` に一元化）