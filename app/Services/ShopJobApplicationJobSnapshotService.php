<?php

namespace App\Services;

use DateTimeInterface;
use Illuminate\Support\Facades\Schema;

class ShopJobApplicationJobSnapshotService
{
    /**
     * 申請 INSERT 用。存在する `shop_job_applications.applied_*` カラムだけ埋める。
     *
     * @return array<string, mixed>
     */
    public function snapshotColumnsForApplication(object $job): array
    {
        $out = [];

        $map = [
            'applied_regular_status' => ['regular_status', 'status'],
            'applied_regular_hourly_wage' => ['regular_hourly_wage', 'hourly_wage_regular'],
            'applied_norma_day' => ['norma_day'],
            'applied_norma_hours' => ['norma_hours', 'hours_day', 'normal_time'],
            'applied_bonus_reward' => ['bonus_reward', 'noruma_reward'],
            'applied_bonus_remarks' => ['bonus_remarks', 'noruma_reward2'],
            'applied_bonus_condition' => ['bonus_condition'],
            'applied_trial_hourly_wage' => ['trial_hourly_wage'],
            'applied_trial_status' => ['trial_status', 'has_trial'],
            'applied_has_help' => ['has_help'],
            'applied_help_hourly_wage' => ['help_hourly_wage'],
            'applied_help_status' => ['help_status'],
            'applied_working_day' => ['working_day'],
            'applied_working_hours' => ['working_hours'],
            'applied_regular_holiday' => ['regular_holiday'],
            'applied_qualification' => ['qualification'],
            'applied_shift_time_start' => ['shift_time_start'],
            'applied_shift_time_end' => ['shift_time_end'],
            'applied_shift_end_is_last' => ['shift_end_is_last'],
            'applied_regular_hourly_wage_max' => ['regular_hourly_wage_max'],
            'applied_trial_hourly_wage_max' => ['trial_hourly_wage_max'],
            'applied_help_hourly_wage_max' => ['help_hourly_wage_max'],
        ];

        foreach ($map as $column => $jobKeys) {
            if (!Schema::hasColumn('shop_job_applications', $column)) {
                continue;
            }
            $raw = $this->firstExistingJobValue($job, $jobKeys);
            $out[$column] = $this->normalizeSnapshotValue($column, $raw);
        }

        if (Schema::hasColumn('shop_job_applications', 'applied_bonus_condition')) {
            $cond = $out['applied_bonus_condition'] ?? null;
            if (($cond === null || $cond === '') && property_exists($job, 'noruma_cond') && $job->noruma_cond !== null && $job->noruma_cond !== '') {
                $meta = json_decode((string) $job->noruma_cond, true);
                if (is_array($meta) && !empty($meta['bonus_condition'])) {
                    $out['applied_bonus_condition'] = trim((string) $meta['bonus_condition']);
                }
            }
        }

        $notNullDefaults = [
            'applied_regular_status' => 0,
            'applied_trial_status' => 0,
            'applied_has_help' => 0,
            'applied_help_status' => 0,
            'applied_shift_end_is_last' => 0,
        ];
        foreach ($notNullDefaults as $col => $def) {
            if (!Schema::hasColumn('shop_job_applications', $col)) {
                continue;
            }
            if (!array_key_exists($col, $out) || $out[$col] === null) {
                $out[$col] = $def;
            }
        }

        return $out;
    }

    private function firstExistingJobValue(object $job, array $jobKeys): mixed
    {
        foreach ($jobKeys as $key) {
            if (property_exists($job, $key)) {
                return $job->{$key};
            }
        }

        return null;
    }

    private function normalizeSnapshotValue(string $column, mixed $raw): mixed
    {
        if ($raw instanceof DateTimeInterface) {
            if (str_contains($column, 'time')) {
                return $raw->format('H:i:s');
            }

            return $raw->format('Y-m-d H:i:s');
        }

        return $raw;
    }
}
