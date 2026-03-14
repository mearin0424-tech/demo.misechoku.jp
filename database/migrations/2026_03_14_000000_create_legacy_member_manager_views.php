<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS members');
        DB::statement('DROP VIEW IF EXISTS managers');

        DB::statement(<<<'SQL'
CREATE VIEW members AS
SELECT
    casts.id,
    casts.email,
    casts.password,
    casts.status,
    casts.status AS approval,
    casts.identity_status,
    casts.last_login_at,
    casts.remember_token,
    casts.created_at,
    casts.updated_at,
    casts.deleted_at,
    CASE WHEN casts.deleted_at IS NULL THEN 0 ELSE 1 END AS del_flg,
    cast_profiles.nickname,
    cast_profiles.name,
    cast_profiles.name_kana AS kana,
    cast_profiles.birthday,
    YEAR(cast_profiles.birthday) AS birthday_y,
    MONTH(cast_profiles.birthday) AS birthday_m,
    DAY(cast_profiles.birthday) AS birthday_d,
    cast_profiles.gender,
    cast_profiles.zip,
    cast_profiles.pref,
    cast_profiles.city,
    cast_profiles.addr1,
    cast_profiles.addr2,
    cast_profiles.addr3,
    cast_profiles.tel,
    cast_profiles.height,
    cast_profiles.weight,
    cast_profiles.bust AS b,
    cast_profiles.waist AS w,
    cast_profiles.hip AS h,
    cast_profiles.shift,
    cast_profiles.profession,
    cast_profiles.exp,
    cast_profiles.years_exp,
    cast_profiles.where_work,
    cast_profiles.pr,
    cast_profiles.charm_point,
    cast_profiles.memo,
    cast_profiles.ng_reason,
    cast_profiles.latitude,
    cast_profiles.longitude,
    NULL AS line_user_id,
    NULL AS line_notify_token,
    0 AS matching,
    0 AS release,
    0 AS footprints,
    NULL AS shop_name
FROM casts
LEFT JOIN cast_profiles ON casts.id = cast_profiles.cast_id
SQL);

        DB::statement(<<<'SQL'
CREATE VIEW managers AS
SELECT
    shop_managers.*,
    shop_profiles.shop_name
FROM shop_managers
LEFT JOIN shop_profiles ON shop_managers.shop_id = shop_profiles.shop_id
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS members');
        DB::statement('DROP VIEW IF EXISTS managers');
    }
};
