-- =============================================================================
-- ### demo function and data for test ###
-- Refresh only the image rows for test personas (cast_images / shop_images).
-- Safe to run multiple times without touching applications / deposits / talks.
--
-- Use cases:
--   1. Change picsum / randomuser / loremflickr seed to get different-looking
--      images without rerunning the full test_reset.sql.
--   2. Reset back to the default placeholders after manually uploading via the
--      app UI messed up the layout.
--   3. Switch between "external placeholder URL" and "no-image fallback" mode
--      quickly (comment out the INSERT blocks to test the fallback).
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
-- Cast portrait images (female-only via randomuser.me / picsum.photos)
-- Change the numeric index in the URL to swap the person.
-- ------------------------------------------------------------------
INSERT INTO `cast_images` (`cast_id`, `image_path`, `type`, `status`, `is_main`, `main_order`, `created_at`, `updated_at`) VALUES
-- c001 with a main + 2 sub photos (exercises the horizontal photo swiper)
('c00000001', 'https://randomuser.me/api/portraits/women/25.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000001', 'https://picsum.photos/seed/misaki2/400/500', 1, 0, 0, 1, NOW(), NOW()),
('c00000001', 'https://picsum.photos/seed/misaki3/400/500', 1, 0, 0, 2, NOW(), NOW()),
-- c002 with a main + 1 sub
('c00000002', 'https://randomuser.me/api/portraits/women/32.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000002', 'https://picsum.photos/seed/yui2/400/500', 1, 0, 0, 1, NOW(), NOW()),
-- c003 - c010 main only
('c00000003', 'https://randomuser.me/api/portraits/women/47.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000004', 'https://randomuser.me/api/portraits/women/58.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000005', 'https://randomuser.me/api/portraits/women/12.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000006', 'https://randomuser.me/api/portraits/women/68.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000007', 'https://randomuser.me/api/portraits/women/71.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000008', 'https://randomuser.me/api/portraits/women/89.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000009', 'https://randomuser.me/api/portraits/women/44.jpg', 1, 0, 1, 0, NOW(), NOW()),
('c00000010', 'https://randomuser.me/api/portraits/women/55.jpg', 1, 0, 1, 0, NOW(), NOW());

-- ------------------------------------------------------------------
-- Shop images (nightclub / bar / lounge / cafe / snack themes via loremflickr)
-- lock=<n> makes the choice deterministic. Change the number to swap.
-- ------------------------------------------------------------------
INSERT INTO `shop_images` (`shop_id`, `image_path`, `type`, `is_main`, `main_order`, `created_at`, `updated_at`) VALUES
-- s001 CLUB LUMINOUS (Ginza high-end club) - 5 photos
('s00000001', 'https://loremflickr.com/400/500/nightclub,elegant,gold?lock=101', 1, 1, 0, NOW(), NOW()),
('s00000001', 'https://loremflickr.com/400/500/nightclub,champagne?lock=102',      1, 0, 1, NOW(), NOW()),
('s00000001', 'https://loremflickr.com/400/500/lounge,vip,luxury?lock=103',        1, 0, 2, NOW(), NOW()),
('s00000001', 'https://loremflickr.com/400/500/bar,counter,elegant?lock=104',      1, 0, 3, NOW(), NOW()),
('s00000001', 'https://loremflickr.com/400/500/nightclub,interior?lock=105',       1, 0, 4, NOW(), NOW()),
-- s002 CUTE club (Roppongi pop) - 4 photos
('s00000002', 'https://loremflickr.com/400/500/bar,pink,neon?lock=201',           1, 1, 0, NOW(), NOW()),
('s00000002', 'https://loremflickr.com/400/500/cocktail,pink?lock=202',           1, 0, 1, NOW(), NOW()),
('s00000002', 'https://loremflickr.com/400/500/nightclub,neon,pink?lock=203',     1, 0, 2, NOW(), NOW()),
('s00000002', 'https://loremflickr.com/400/500/bar,interior,pink?lock=204',       1, 0, 3, NOW(), NOW()),
-- s003 CAFE MOCHA (Shibuya cafe) - 3 photos
('s00000003', 'https://loremflickr.com/400/500/cafe,cozy,drink?lock=301',         1, 1, 0, NOW(), NOW()),
('s00000003', 'https://loremflickr.com/400/500/cafe,latte,art?lock=302',          1, 0, 1, NOW(), NOW()),
('s00000003', 'https://loremflickr.com/400/500/cafe,interior?lock=303',           1, 0, 2, NOW(), NOW()),
-- s004 SNACK PEARL (Shinjuku snack) - 4 photos
('s00000004', 'https://loremflickr.com/400/500/bar,neon,tokyo?lock=401',          1, 1, 0, NOW(), NOW()),
('s00000004', 'https://loremflickr.com/400/500/snack,bar,japan?lock=402',         1, 0, 1, NOW(), NOW()),
('s00000004', 'https://loremflickr.com/400/500/bar,karaoke?lock=403',             1, 0, 2, NOW(), NOW()),
('s00000004', 'https://loremflickr.com/400/500/bar,drinks,night?lock=404',        1, 0, 3, NOW(), NOW()),
-- s005 LOUNGE STAR (Ikebukuro lounge) - 5 photos
('s00000005', 'https://loremflickr.com/400/500/lounge,cocktail,gold?lock=501',    1, 1, 0, NOW(), NOW()),
('s00000005', 'https://loremflickr.com/400/500/lounge,sofa,vip?lock=502',         1, 0, 1, NOW(), NOW()),
('s00000005', 'https://loremflickr.com/400/500/nightclub,gold,luxury?lock=503',   1, 0, 2, NOW(), NOW()),
('s00000005', 'https://loremflickr.com/400/500/cocktail,bar,elegant?lock=504',    1, 0, 3, NOW(), NOW()),
('s00000005', 'https://loremflickr.com/400/500/lounge,neon,night?lock=505',       1, 0, 4, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Image rows refreshed.' AS status,
       (SELECT COUNT(*) FROM cast_images) AS cast_images,
       (SELECT COUNT(*) FROM shop_images) AS shop_images;
