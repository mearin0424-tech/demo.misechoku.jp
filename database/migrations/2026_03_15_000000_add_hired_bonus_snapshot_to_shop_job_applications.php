<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_job_applications', function (Blueprint $table) {
            $table->integer('hired_bonus_amount')->nullable()->after('normal_time');
            $table->text('hired_bonus_condition')->nullable()->after('hired_bonus_amount');
        });

        // 既存の採用済み（status=4）でスナップショット未設定の行を求人からバックフィル
        $applications = DB::table('shop_job_applications')
            ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->where('shop_job_applications.status', 4)
            ->whereNull('shop_job_applications.hired_bonus_amount')
            ->select(
                'shop_job_applications.id',
                'shop_jobs.noruma_reward',
                'shop_jobs.hourly_wage_regular',
                'shop_jobs.noruma_cond'
            )
            ->get();

        foreach ($applications as $row) {
            $bonusAmount = (int) ($row->noruma_reward ?? $row->hourly_wage_regular ?? 0);
            $bonusCondition = '';
            if (!empty($row->noruma_cond)) {
                $meta = json_decode($row->noruma_cond, true);
                $bonusCondition = trim((string) ($meta['bonus_condition'] ?? ''));
            }
            DB::table('shop_job_applications')
                ->where('id', $row->id)
                ->update([
                    'hired_bonus_amount' => $bonusAmount,
                    'hired_bonus_condition' => $bonusCondition,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('shop_job_applications', function (Blueprint $table) {
            $table->dropColumn(['hired_bonus_amount', 'hired_bonus_condition']);
        });
    }
};
