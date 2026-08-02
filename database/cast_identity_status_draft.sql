-- =============================================================================
-- キャスト本人確認書類：STATUS_DRAFT (0) の追加（コメント整備）
--
-- 挙動変更（2026-08-02）:
--   - アップロード直後は status=0 (下書き) で保存されるようになった
--   - ユーザが明示的に「運営に提出する」ボタンを押すと status=1 (未審査) に遷移し
--     admin ダッシュボードの審査対象となる
--   - 既存のデータには影響なし（DEFAULT は明示的に 0 に変更するが、既存行の値は変わらない）
--
-- 実行方法:
--   mysql -u root -p misechoku < database/cast_identity_status_draft.sql
-- =============================================================================

-- カラム定義自体は既存と互換（tinyint / NOT NULL）。DEFAULT と COMMENT のみ更新。
ALTER TABLE `cast_identity_documents`
    MODIFY COLUMN `status` tinyint NOT NULL DEFAULT 0
        COMMENT '0:下書き(未提出), 1:未審査(提出済み), 2:承認済, 3:不備・却下';
