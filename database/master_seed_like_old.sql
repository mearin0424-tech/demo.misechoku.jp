-- 新 masters 向けの初期投入SQL
-- 対象テーブル:
--   - ng_words
--   - review_contents
--   - tags
--
-- 旧データをもとに、新スキーマへ寄せて投入する想定です。
-- 既存データとID/uniqueキーが衝突した場合は更新します。

SET NAMES utf8mb4;

-- ------------------------------------------------------------------
-- 1. NGワード
-- ------------------------------------------------------------------
INSERT INTO `ng_words` (`word`, `is_active`, `created_at`, `updated_at`) VALUES
('個人連絡先', 1, NOW(), NOW()),
('連絡先交換', 1, NOW(), NOW()),
('LINE交換', 1, NOW(), NOW()),
('ライン交換', 1, NOW(), NOW()),
('Instagram', 1, NOW(), NOW()),
('インスタ', 1, NOW(), NOW()),
('X交換', 1, NOW(), NOW()),
('Twitter交換', 1, NOW(), NOW()),
('カカオ', 1, NOW(), NOW()),
('Kakao', 1, NOW(), NOW()),
('Telegram', 1, NOW(), NOW()),
('テレグラム', 1, NOW(), NOW()),
('直引き', 1, NOW(), NOW()),
('店外', 1, NOW(), NOW()),
('裏オプ', 1, NOW(), NOW()),
('本番', 1, NOW(), NOW()),
('ホ別', 1, NOW(), NOW()),
('会うだけ', 1, NOW(), NOW()),
('条件あり', 1, NOW(), NOW()),
('個別契約', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `is_active` = VALUES(`is_active`),
  `updated_at` = VALUES(`updated_at`);

-- ------------------------------------------------------------------
-- 2. レビュー設問マスタ
-- review_contents:
--   旧: content / del_flg
--   新: name / sort_order / is_active
-- ------------------------------------------------------------------
INSERT INTO `review_contents` (`id`, `name`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '店内は清潔に保たれていますか？', 1, 1, NOW(), NOW()),
(2, 'スタッフの対応は親切ですか？', 2, 1, NOW(), NOW()),
(3, '店内の雰囲気はリラックスできますか？', 3, 1, NOW(), NOW()),
(4, '給料や待遇に満足していますか？', 4, 1, NOW(), NOW()),
(5, 'シフトの調整や融通が効きますか？', 5, 1, NOW(), NOW()),
(6, '来店するお客様の質に満足していますか？', 6, 1, NOW(), NOW()),
(7, '店舗内での安全性は確保されていますか？', 7, 1, NOW(), NOW()),
(8, '教育やサポート体制は十分ですか？', 8, 1, NOW(), NOW()),
(9, '店舗での働きやすさに満足していますか？', 9, 1, NOW(), NOW()),
(10, '店舗の立地条件は良いですか？', 10, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `sort_order` = VALUES(`sort_order`),
  `is_active` = VALUES(`is_active`),
  `updated_at` = VALUES(`updated_at`);

-- ------------------------------------------------------------------
-- 3. タグマスタ
-- 旧 cast_tags / tags / 各種特徴タグを新 tags テーブルへ集約
-- tags:
--   id / type / name / created_at
-- ------------------------------------------------------------------
INSERT INTO `tags` (`id`, `type`, `name`, `created_at`) VALUES
-- 給与・待遇
(1, 'salary', '1ヶ月払い', NOW()),
(2, 'salary', '15日払い', NOW()),
(3, 'salary', '10日払い', NOW()),
(4, 'salary', '1週間払い', NOW()),
(5, 'salary', '翌日払い', NOW()),
(6, 'salary', '全額日払い', NOW()),
(7, 'salary', '日払い可', NOW()),
(8, 'salary', '交通費支給', NOW()),
(9, 'salary', '高額時給', NOW()),
(10, 'salary', '高額バック支給', NOW()),
(11, 'salary', '入店祝い金支給', NOW()),
(12, 'salary', '給料手渡し', NOW()),
(13, 'salary', '給料UP', NOW()),

-- 働き方
(14, 'howto', '週1からOK', NOW()),
(15, 'howto', '短期OK', NOW()),
(16, 'howto', '1日1h以内', NOW()),
(17, 'howto', '1日2h以内', NOW()),
(18, 'howto', '1日3h以内', NOW()),
(19, 'howto', '1日4h以内', NOW()),
(20, 'howto', '未経験者歓迎', NOW()),
(21, 'howto', '出稼ぎOK', NOW()),
(22, 'howto', '終電上がりOK', NOW()),
(23, 'howto', 'WワークOK', NOW()),

-- メリット・待遇
(24, 'merit', 'レンタル衣装有り', NOW()),
(25, 'merit', '服装自由', NOW()),
(26, 'merit', 'ヘアメイク有り', NOW()),
(27, 'merit', 'ヘアメイク無料', NOW()),
(28, 'merit', 'ヘアメイク不要', NOW()),
(29, 'merit', '髪型自由', NOW()),
(30, 'merit', '服装自由', NOW()),
(31, 'merit', '小物レンタル無料', NOW()),
(32, 'merit', 'レンタルドレス', NOW()),
(33, 'merit', 'レンタル衣装', NOW()),
(34, 'merit', 'レンタル衣装無料', NOW()),
(35, 'merit', '手ぶらで体入OK', NOW()),
(36, 'merit', '送り有り', NOW()),
(37, 'merit', '迎え有り', NOW()),
(38, 'merit', '駅からスグ', NOW()),
(39, 'merit', '終電上がりOK', NOW()),
(40, 'merit', '早上げ無し', NOW()),
(41, 'merit', 'ノルマ無し', NOW()),
(42, 'merit', '福利厚生・提携先割引有り', NOW()),
(43, 'merit', '早上がり有り', NOW()),
(44, 'merit', 'ドリンクバック', NOW()),
(45, 'merit', '指名バック', NOW()),
(46, 'merit', 'ヘアメイク提携割引有り', NOW()),
(47, 'merit', '託児用提携割引有り', NOW()),
(48, 'merit', '衣装割引き有り', NOW()),
(49, 'merit', '駅徒歩5分', NOW()),
(50, 'merit', '駅徒歩10分', NOW()),
(51, 'merit', '売上バック有り', NOW()),
(52, 'merit', '同伴バック有り', NOW()),
(53, 'merit', 'シャンパンバック有り', NOW()),
(54, 'merit', 'その他バック有り', NOW()),

-- 店舗特徴
(55, 'feature', '未経験', NOW()),
(56, 'feature', 'シングルマザーOK', NOW()),
(57, 'feature', '経験者優遇', NOW()),
(58, 'feature', '学生歓迎', NOW()),
(59, 'feature', '主婦歓迎', NOW()),
(60, 'feature', 'ブランク歓迎', NOW()),
(61, 'feature', 'お酒NG歓迎', NOW()),
(62, 'feature', 'ニューオープン', NOW()),
(63, 'feature', '登録制有り', NOW()),
(64, 'feature', '日曜営業', NOW()),
(65, 'feature', '10代歓迎', NOW()),
(66, 'feature', '30代歓迎', NOW()),
(67, 'feature', '40代歓迎', NOW()),
(68, 'feature', '50代歓迎', NOW()),
(69, 'feature', 'コロナウイルス対策実施', NOW()),
(70, 'feature', 'タトゥーOK', NOW()),
(71, 'feature', '禁煙店', NOW()),
(72, 'feature', '定休日無し', NOW()),
(73, 'feature', 'ぽっちゃりOK', NOW()),
(74, 'feature', '何回か体入OK', NOW()),

-- 設備
(75, 'facility', '駐車場有り', NOW()),
(76, 'facility', '車通勤OK', NOW()),
(77, 'facility', '寮有り', NOW()),
(78, 'facility', '即日入居可寮有り', NOW()),
(79, 'facility', '託児所有り', NOW()),
(80, 'facility', '個人ロッカー有り', NOW()),
(81, 'facility', 'キャスト専用トイレ有り', NOW()),

-- キャスト特徴
(82, 'casttag', 'スレンダー', NOW()),
(83, 'casttag', '普通', NOW()),
(84, 'casttag', 'グラマー', NOW()),
(85, 'casttag', 'ぽっちゃり', NOW()),
(86, 'casttag', '高長身', NOW()),
(87, 'casttag', '小柄', NOW()),
(88, 'casttag', 'スタイル抜群', NOW()),
(89, 'casttag', 'キレイ系', NOW()),
(90, 'casttag', '可愛い系', NOW()),
(91, 'casttag', 'セクシー系', NOW()),
(92, 'casttag', '巨乳', NOW()),
(93, 'casttag', 'ギャル', NOW()),
(94, 'casttag', '清楚系', NOW()),
(95, 'casttag', 'お姉さん系', NOW()),
(96, 'casttag', '癒し系', NOW()),
(97, 'casttag', '萌え系', NOW()),
(98, 'casttag', 'モデル/元モデル', NOW()),
(99, 'casttag', 'ハーフ/ハーフ顔', NOW()),
(100, 'casttag', 'アイドル経験有り', NOW()),
(101, 'casttag', 'インフルエンサー', NOW()),
(102, 'casttag', '著名人', NOW()),
(103, 'casttag', 'OL/一般職', NOW()),
(104, 'casttag', '学生', NOW()),
(105, 'casttag', '顔出しOK', NOW()),
(106, 'casttag', '顔出しNG', NOW()),
(107, 'casttag', '初心者/未経験', NOW()),

-- キャスト性格
(108, 'casttag2', '社交的', NOW()),
(109, 'casttag2', '明るい', NOW()),
(110, 'casttag2', 'おしゃべり上手', NOW()),
(111, 'casttag2', 'わいわい系', NOW()),
(112, 'casttag2', 'パリピ系', NOW()),
(113, 'casttag2', 'おとなしめ', NOW()),
(114, 'casttag2', 'おっとり', NOW()),
(115, 'casttag2', 'しっとり', NOW()),
(116, 'casttag2', '接客上手', NOW()),
(117, 'casttag2', 'お酒飲める人', NOW()),
(118, 'casttag2', 'お酒苦手', NOW()),
(119, 'casttag2', '姉御肌', NOW()),
(120, 'casttag2', '妹気質', NOW()),
(121, 'casttag2', '連絡マメ', NOW()),

-- お店の雰囲気
(122, 'atmosphere', 'わいわい', NOW()),
(123, 'atmosphere', 'しっとり', NOW()),
(124, 'atmosphere', 'おっとり', NOW()),
(125, 'atmosphere', 'アットホーム', NOW()),
(126, 'atmosphere', '大型店', NOW()),
(127, 'atmosphere', '中型店', NOW()),
(128, 'atmosphere', '小さいお店', NOW()),
(129, 'atmosphere', '高級店', NOW()),
(130, 'atmosphere', '大衆店', NOW()),
(131, 'atmosphere', 'キャスト多数', NOW()),
(132, 'atmosphere', '少人数', NOW()),
(133, 'atmosphere', '上下関係無し', NOW()),
(134, 'atmosphere', '派閥無し', NOW()),
(135, 'atmosphere', '新規オープン', NOW()),
(136, 'atmosphere', 'リニューアルオープン', NOW()),
(137, 'atmosphere', 'ニューオープン', NOW()),
(138, 'atmosphere', 'ステージ有り', NOW()),
(139, 'atmosphere', 'カウンターのみ', NOW()),
(140, 'atmosphere', 'カラオケ有り', NOW()),
(141, 'atmosphere', 'カラオケ無し', NOW()),
(142, 'atmosphere', 'VIPルール完備', NOW())
ON DUPLICATE KEY UPDATE
  `type` = VALUES(`type`),
  `name` = VALUES(`name`);
