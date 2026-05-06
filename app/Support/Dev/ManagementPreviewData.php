<?php

namespace App\Support\Dev;

use Illuminate\Http\Request;

/**
 * 開発用プレビューのダミーデータ提供クラス
 *
 * 実 Blade（shops.mypage.management 等）にそのまま流し込んで
 * ローカル環境で UI 確認するために使用する。
 * 本番環境では使用されない（ルートが local/development でのみ登録される）。
 */
final class ManagementPreviewData
{
    /**
     * 店舗 採用・入金管理 統合ページ用のダミーデータ
     */
    public static function shop(Request $request): array
    {
        $tab = $request->query('tab') === 'payment' ? 'payment' : 'recruit';
        $today = date('Y/m/d');
        $yesterday = date('Y/m/d', strtotime('-1 day'));
        $weekAgo = date('Y/m/d', strtotime('-7 day'));
        $weekLater = date('Y/m/d', strtotime('+7 day'));

        $applications = [
            // 1. 進行中 / やり取り中
            [
                'id' => 1001,
                'cast_id' => 'cast-001',
                'status' => 1,
                'status_label' => 'やり取り中',
                'pattern' => 'P1',
                'pattern_label' => '新規採用',
                'job_kind_label' => '本入店',
                'status_display_label' => 'やり取り中',
                'result_date' => null,
                'real_start_date' => null,
                'created_at' => $weekAgo,
                'cast_name' => '山田 花子',
                'cast_avatar_url' => null,
                'rejection_reason' => null,
                'is_delayed' => false,
                'delay_message' => '',
                'applied_summary_lines' => [
                    '希望時給: 5,000円',
                    '出勤可能日: 水・木・金',
                ],
                'hired_regular_hourly_wage' => null,
                'hired_regular_hourly_wage_input' => '',
                'can_edit_hired_wage' => false,
                'is_decision_overdue' => false,
            ],
            // 2. 進行中 / 面談日決定 / 面談日超過 ← バッジ点灯対象
            [
                'id' => 1002,
                'cast_id' => 'cast-002',
                'status' => 3,
                'status_label' => '面談日決定',
                'pattern' => 'P1',
                'pattern_label' => '新規採用',
                'job_kind_label' => '本入店',
                'status_display_label' => '面談日決定',
                'result_date' => $yesterday,
                'real_start_date' => null,
                'created_at' => $weekAgo,
                'cast_name' => '佐藤 美咲',
                'cast_avatar_url' => null,
                'rejection_reason' => null,
                'is_delayed' => false,
                'delay_message' => '',
                'applied_summary_lines' => [
                    '希望時給: 4,500円',
                    '出勤可能日: 月〜金',
                    'ボーナス条件: 月20日勤務',
                ],
                'hired_regular_hourly_wage' => null,
                'hired_regular_hourly_wage_input' => '',
                'can_edit_hired_wage' => false,
                'is_decision_overdue' => true, // ← 面談日超過
            ],
            // 3. 採用 / 体験採用 (P1) - 採用時給編集可能
            [
                'id' => 1003,
                'cast_id' => 'cast-003',
                'status' => 4,
                'status_label' => '体験採用',
                'pattern' => 'P1',
                'pattern_label' => '新規採用',
                'job_kind_label' => '体験入店',
                'status_display_label' => '体験採用',
                'result_date' => $weekAgo,
                'real_start_date' => $today,
                'created_at' => date('Y/m/d', strtotime('-14 day')),
                'cast_name' => '鈴木 れい',
                'cast_avatar_url' => null,
                'rejection_reason' => null,
                'is_delayed' => false,
                'delay_message' => '',
                'applied_summary_lines' => [
                    '希望時給: 5,500円',
                    '出勤可能日: 火・水・土',
                ],
                'hired_regular_hourly_wage' => 5500,
                'hired_regular_hourly_wage_input' => '5500',
                'can_edit_hired_wage' => true,
                'is_decision_overdue' => false,
            ],
            // 4. 採用 / ヘルプ採用 (P2)
            [
                'id' => 1004,
                'cast_id' => 'cast-004',
                'status' => 4,
                'status_label' => 'ヘルプ採用',
                'pattern' => 'P2',
                'pattern_label' => 'ヘルプ',
                'job_kind_label' => 'ヘルプ',
                'status_display_label' => 'ヘルプ採用',
                'result_date' => $weekAgo,
                'real_start_date' => $weekLater,
                'created_at' => date('Y/m/d', strtotime('-10 day')),
                'cast_name' => '高橋 ゆい',
                'cast_avatar_url' => null,
                'rejection_reason' => null,
                'is_delayed' => false,
                'delay_message' => '',
                'applied_summary_lines' => [
                    'ヘルプ時給: 6,000円',
                    '対応日: 金・土',
                ],
                'hired_regular_hourly_wage' => null,
                'hired_regular_hourly_wage_input' => '',
                'can_edit_hired_wage' => false,
                'is_decision_overdue' => false,
            ],
            // 5. 不採用 / 理由付き
            [
                'id' => 1005,
                'cast_id' => 'cast-005',
                'status' => 5,
                'status_label' => '不採用',
                'pattern' => 'P1',
                'pattern_label' => '新規採用',
                'job_kind_label' => '本入店',
                'status_display_label' => '不採用',
                'result_date' => date('Y/m/d', strtotime('-3 day')),
                'real_start_date' => null,
                'created_at' => date('Y/m/d', strtotime('-21 day')),
                'cast_name' => '田中 ひかり',
                'cast_avatar_url' => null,
                'rejection_reason' => '勤務可能日数が条件と合わなかったため、見送りとなりました。',
                'is_delayed' => false,
                'delay_message' => '',
                'applied_summary_lines' => [],
                'hired_regular_hourly_wage' => null,
                'hired_regular_hourly_wage_input' => '',
                'can_edit_hired_wage' => false,
                'is_decision_overdue' => false,
            ],
        ];

        $invoices = [
            // 1. 入金待ち + 期限超過 ← バッジ点灯
            [
                'id' => 1,
                'title' => '請求書 INV-202604-0012',
                'amount' => 48000,
                'status' => 'pending', // → unpaid
                'date' => date('Y-m-d', strtotime('-3 day')), // 過去日 = delayed
                'invoice_url' => '#',
                'invoice_pdf_url' => '#',
            ],
            // 2. 入金待ち / 期限内
            [
                'id' => 2,
                'title' => '請求書 INV-202605-0001',
                'amount' => 32000,
                'status' => 'pending',
                'date' => date('Y-m-d', strtotime('+5 day')),
                'invoice_url' => '#',
                'invoice_pdf_url' => '#',
            ],
            // 3. 入金済み
            [
                'id' => 3,
                'title' => '請求書 INV-202604-0008',
                'amount' => 56000,
                'status' => 'paid',
                'date' => date('Y-m-d', strtotime('-14 day')),
                'invoice_url' => '#',
                'invoice_pdf_url' => '#',
            ],
        ];

        return [
            'pageId' => 'management',
            'tab' => $tab,
            // 採用
            'applications' => $applications,
            'recruitBadge' => true, // 1件 overdue
            'recruitInProgressCount' => 2,
            // 入金
            'invoices' => $invoices,
            'summary' => [
                'unpaid_total' => 80000,
                'next_settlement' => date('Y-m-d', strtotime('+10 day')),
            ],
            'paymentBadge' => true,
            'paymentPendingCount' => 2,
        ];
    }
}
