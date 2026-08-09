-- =============================================================================
-- ### demo function and data for test ###
-- Refresh only the image rows for test personas (cast_images / shop_images).
-- Safe to run multiple times without touching applications / deposits / talks.
--
-- Image source policy (2026-08 rev):
--   All URLs point to curated, high-resolution Unsplash photos via the stable
--   images.unsplash.com CDN with explicit photo IDs. This replaces the earlier
--   randomuser.me (128px, upscaled and blurry) and loremflickr.com (low-res,
--   frequent off-topic hits) URLs. Query params:
--     w=1200&h=1500  -> 4:5 portrait crop matching the card layout
--     fit=crop       -> Unsplash smart-crop keeps subject centered
--     auto=format    -> serves AVIF/WebP where supported
--     q=80           -> visually lossless at typical card sizes
--
-- Use cases:
--   1. Swap a photo by replacing its Unsplash ID (any valid photo-XXXX id works).
--   2. Reset back to defaults after manually uploading via the app UI.
--   3. Comment out INSERT blocks to test the no-image fallback.
--
-- Usage:
--   mysql -u root -p misechoku < database/test_reset_images.sql
--
-- Prerequisites: test_reset.sql already ran (personas c001-c010 and s001-s005
-- must exist).
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

TRUNCATE TABLE `cast_images`;
TRUNCATE TABLE `shop_images`;

-- ------------------------------------------------------------------
-- Cast portrait images (curated Unsplash female portraits, 1200x1500)
-- Each id is a stable Unsplash photo id; swap freely.
-- ------------------------------------------------------------------
INSERT INTO `cast_images` (`cast_id`, `image_path`, `status`, `is_main`, `main_order`, `created_at`, `updated_at`) VALUES
-- c001 with a main + 2 sub photos (exercises the horizontal photo swiper)
('c00000001', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=1200&h=1500&fit=crop&auto=format&q=80', 0, 1, 0, NOW(), NOW()),
('c00000001', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=1200&h=1500&fit=crop&auto=format&q=80', 0, 0, 1, NOW(), NOW()),
('c00000001', 'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?w=1200&h=1500&fit=crop&auto=format&q=80', 0, 0, 2, NOW(), NOW()),
-- c002 with a main + 1 sub
('c00000002', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=1200&h=1500&fit=crop&auto=format&q=80', 0, 1, 0, NOW(), NOW()),
('c00000002', 'https://images.unsplash.com/photo-1502768040783-423da5fd5fa0?w=1200&h=1500&fit=crop&auto=format&q=80', 0, 0, 1, NOW(), NOW()),
-- c003 - c010 main only
('c00000003', 'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=1200&h=1500&fit=crop&auto=format&q=80', 0, 1, 0, NOW(), NOW()),
('c00000004', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=1200&h=1500&fit=crop&auto=format&q=80',    0, 1, 0, NOW(), NOW()),
('c00000005', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=1200&h=1500&fit=crop&auto=format&q=80', 0, 1, 0, NOW(), NOW()),
('c00000006', 'https://images.unsplash.com/photo-1503104834685-7205e8607eb9?w=1200&h=1500&fit=crop&auto=format&q=80', 0, 1, 0, NOW(), NOW()),
('c00000007', 'https://images.unsplash.com/photo-1554151228-14d9def656e4?w=1200&h=1500&fit=crop&auto=format&q=80',    0, 1, 0, NOW(), NOW()),
('c00000008', 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=1200&h=1500&fit=crop&auto=format&q=80', 0, 1, 0, NOW(), NOW()),
('c00000009', 'https://images.unsplash.com/photo-1489424731084-a5d8b219a5bb?w=1200&h=1500&fit=crop&auto=format&q=80', 0, 1, 0, NOW(), NOW()),
('c00000010', 'https://images.unsplash.com/photo-1523824921871-d6f1a15151f1?w=1200&h=1500&fit=crop&auto=format&q=80', 0, 1, 0, NOW(), NOW());

-- ------------------------------------------------------------------
-- Shop images (curated Unsplash bar/lounge/cafe photos, 1200x1500)
-- Grouped by shop theme so the swiper stays visually coherent.
-- ------------------------------------------------------------------
INSERT INTO `shop_images` (`shop_id`, `image_path`, `type`, `is_main`, `main_order`, `created_at`, `updated_at`) VALUES
-- s001 CLUB LUMINOUS (Ginza high-end club) - 5 photos: luxury / gold / champagne
('s00000001', 'https://images.unsplash.com/photo-1543007630-9710e4a00a20?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 1, 0, NOW(), NOW()),
('s00000001', 'https://images.unsplash.com/photo-1470337458703-46ad1756a187?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 1, NOW(), NOW()),
('s00000001', 'https://images.unsplash.com/photo-1533777857889-4be7c70b33f7?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 2, NOW(), NOW()),
('s00000001', 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 3, NOW(), NOW()),
('s00000001', 'https://images.unsplash.com/photo-1519671482749-fd09be7ccebf?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 4, NOW(), NOW()),
-- s002 CUTE club (Roppongi pop) - 4 photos: pink / neon / cocktail
('s00000002', 'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 1, 0, NOW(), NOW()),
('s00000002', 'https://images.unsplash.com/photo-1572116469696-31de0f17cc34?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 1, NOW(), NOW()),
('s00000002', 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 2, NOW(), NOW()),
('s00000002', 'https://images.unsplash.com/photo-1541544181051-e46607bc22a4?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 3, NOW(), NOW()),
-- s003 CAFE MOCHA (Shibuya cafe) - 3 photos: latte / cafe interior
('s00000003', 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 1, 0, NOW(), NOW()),
('s00000003', 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 1, NOW(), NOW()),
('s00000003', 'https://images.unsplash.com/photo-1466978913421-dad2ebd01d17?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 2, NOW(), NOW()),
-- s004 SNACK PEARL (Shinjuku snack) - 4 photos: Japanese bar / karaoke
('s00000004', 'https://images.unsplash.com/photo-1560840067-ddcaeb7831d2?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 1, 0, NOW(), NOW()),
('s00000004', 'https://images.unsplash.com/photo-1493857671505-72967e2e2760?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 1, NOW(), NOW()),
('s00000004', 'https://images.unsplash.com/photo-1554306297-0c86e837d24b?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 2, NOW(), NOW()),
('s00000004', 'https://images.unsplash.com/photo-1541873676-a18131494184?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 3, NOW(), NOW()),
-- s005 LOUNGE STAR (Ikebukuro lounge) - 5 photos: lounge / cocktail / elegant
('s00000005', 'https://images.unsplash.com/photo-1517697471339-4aa32003c11a?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 1, 0, NOW(), NOW()),
('s00000005', 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 1, NOW(), NOW()),
('s00000005', 'https://images.unsplash.com/photo-1508615039623-a25605d2b022?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 2, NOW(), NOW()),
('s00000005', 'https://images.unsplash.com/photo-1424847651672-bf20a4b0982b?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 3, NOW(), NOW()),
('s00000005', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=1200&h=1500&fit=crop&auto=format&q=80', 1, 0, 4, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Image rows refreshed.' AS status,
       (SELECT COUNT(*) FROM cast_images) AS cast_images,
       (SELECT COUNT(*) FROM shop_images) AS shop_images;
