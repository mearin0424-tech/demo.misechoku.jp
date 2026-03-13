<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class BillingManagementService
{
    public const STATUS_CAST_REQUESTED = 1;
    public const STATUS_SHOP_APPROVED = 2;
    public const STATUS_INVOICE_ISSUED = 3;
    public const STATUS_SHOP_PAYMENT_REPORTED = 4;
    public const STATUS_SHOP_PAYMENT_CONFIRMED = 5;
    public const STATUS_CAST_TRANSFERRED = 6;
    public const STATUS_COMPLETED = 7;

    private const SYSTEM_FEE_RATE = 0.10;
    private const INVOICE_DUE_DAYS = 7;

    public function normalizeBankAccountData(array $data): array
    {
        $accountHolderName = trim((string) ($data['account_holder_name'] ?? ''));
        $accountName = trim((string) ($data['account_name'] ?? ''));

        return [
            'bank_name' => trim((string) ($data['bank_name'] ?? '')),
            'branch_name' => $this->nullIfEmpty(trim((string) ($data['branch_name'] ?? ''))),
            'account_type' => trim((string) ($data['account_type'] ?? '')),
            'account_number' => preg_replace('/\D+/', '', (string) ($data['account_number'] ?? '')) ?? '',
            'account_holder_name' => $accountHolderName,
            'account_name' => $accountName !== '' ? $accountName : $accountHolderName,
        ];
    }

    public function getAdminBankAccount(): ?object
    {
        $account = DB::table('admin_bank_accounts')
            ->select($this->bankAccountSelectColumns('admin_bank_accounts'))
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first();

        return $this->normalizeBankAccountRecord($account);
    }

    public function saveAdminBankAccount(array $data): void
    {
        $data = $this->normalizeBankAccountData($data);

        DB::table('admin_bank_accounts')->update(['is_active' => false, 'updated_at' => now()]);

        DB::table('admin_bank_accounts')->insert($this->filterExistingColumns('admin_bank_accounts', [
            'bank_name' => $data['bank_name'],
            'branch_name' => $data['branch_name'] ?? null,
            'account_type' => $data['account_type'],
            'account_number' => $data['account_number'],
            'account_holder_name' => $data['account_holder_name'],
            'account_name' => $data['account_name'],
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    public function getCastBankAccount(string $castId): ?object
    {
        $account = DB::table('bank_accounts')
            ->select($this->bankAccountSelectColumns('bank_accounts'))
            ->where('member_id', $castId)
            ->first();

        return $this->normalizeBankAccountRecord($account);
    }

    public function saveCastBankAccount(string $castId, array $data): void
    {
        $data = $this->normalizeBankAccountData($data);

        DB::table('bank_accounts')->updateOrInsert(
            ['member_id' => $castId],
            $this->filterExistingColumns('bank_accounts', [
                'bank_name' => $data['bank_name'],
                'branch_name' => $data['branch_name'] ?? null,
                'account_type' => $data['account_type'],
                'account_number' => $data['account_number'],
                'account_holder_name' => $data['account_holder_name'],
                'account_name' => $data['account_name'],
                'updated_at' => now(),
                'created_at' => now(),
            ])
        );
    }

    public function getShopBankAccount(string $shopId): ?object
    {
        $account = DB::table('bank_account_shops')
            ->select($this->bankAccountSelectColumns('bank_account_shops'))
            ->where('shop_id', $shopId)
            ->first();

        return $this->normalizeBankAccountRecord($account);
    }

    public function saveShopBankAccount(string $shopId, array $data): void
    {
        $data = $this->normalizeBankAccountData($data);

        DB::table('bank_account_shops')->updateOrInsert(
            ['shop_id' => $shopId],
            $this->filterExistingColumns('bank_account_shops', [
                'bank_name' => $data['bank_name'],
                'branch_name' => $data['branch_name'] ?? null,
                'account_type' => $data['account_type'],
                'account_number' => $data['account_number'],
                'account_holder_name' => $data['account_holder_name'],
                'account_name' => $data['account_name'],
                'updated_at' => now(),
                'created_at' => now(),
            ])
        );
    }

    public function getAdminBillingDashboard(): array
    {
        $deposits = $this->getAllDeposits();

        return [
            'deposits' => $deposits,
            'summary' => [
                'invoice_pending' => collect($deposits)->where('status_code', self::STATUS_SHOP_APPROVED)->count(),
                'payment_confirmation_pending' => collect($deposits)->where('status_code', self::STATUS_SHOP_PAYMENT_REPORTED)->count(),
                'cast_transfer_pending' => collect($deposits)->where('status_code', self::STATUS_SHOP_PAYMENT_CONFIRMED)->count(),
                'invoice_total' => collect($deposits)->sum('invoice_amount'),
            ],
        ];
    }

    private function nullIfEmpty(string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function getPendingTasks(): array
    {
        return collect($this->getAllDeposits())
            ->filter(fn (array $deposit) => in_array($deposit['status_code'], [
                self::STATUS_SHOP_APPROVED,
                self::STATUS_SHOP_PAYMENT_REPORTED,
                self::STATUS_SHOP_PAYMENT_CONFIRMED,
            ], true))
            ->map(function (array $deposit) {
                $deposit['task_title'] = match ($deposit['status_code']) {
                    self::STATUS_SHOP_APPROVED => '店舗へ請求書を発行する',
                    self::STATUS_SHOP_PAYMENT_REPORTED => '店舗入金を照合する',
                    self::STATUS_SHOP_PAYMENT_CONFIRMED => 'キャストへの振込を実行する',
                    default => '対応不要',
                };

                $deposit['task_due_date'] = match ($deposit['status_code']) {
                    self::STATUS_SHOP_APPROVED => $deposit['invoice_due_date'] ?: now()->addDays(self::INVOICE_DUE_DAYS)->format('Y-m-d'),
                    self::STATUS_SHOP_PAYMENT_REPORTED => $deposit['shop_payment_reported_at'] ?: now()->format('Y-m-d H:i'),
                    self::STATUS_SHOP_PAYMENT_CONFIRMED => $deposit['shop_payment_confirmed_at'] ?: now()->format('Y-m-d H:i'),
                    default => null,
                };

                $deposit['task_actor_label'] = match ($deposit['status_code']) {
                    self::STATUS_SHOP_APPROVED => '運営',
                    self::STATUS_SHOP_PAYMENT_REPORTED => '運営',
                    self::STATUS_SHOP_PAYMENT_CONFIRMED => '運営',
                    default => 'システム',
                };

                $deposit['task_summary'] = match ($deposit['status_code']) {
                    self::STATUS_SHOP_APPROVED => trim((string) ($deposit['bonus_condition'] ?: '店舗承認済みのため、請求書発行へ進めます。')),
                    self::STATUS_SHOP_PAYMENT_REPORTED => '店舗報告金額: ¥' . number_format((int) ($deposit['shop_payment_reported_amount'] ?? 0))
                        . ' / 参照: ' . (($deposit['shop_payment_reference'] ?? '') ?: '未入力'),
                    self::STATUS_SHOP_PAYMENT_CONFIRMED => '振込予定額: ¥' . number_format((int) ($deposit['cast_transfer_amount'] ?? 0))
                        . ' / キャスト口座: ' . (!empty($deposit['has_cast_bank']) ? '登録済み' : '未登録'),
                    default => '',
                };

                $deposit['task_review_summary'] = trim((string) ($deposit['review_comment'] ?? ''));

                return $deposit;
            })
            ->values()
            ->all();
    }

    public function requestDepositForCast(string $castId, array $payload = []): array
    {
        if (!$this->getCastBankAccount($castId)) {
            return ['success' => false, 'message' => '入金申請の前に、キャストの振込先口座を登録してください。'];
        }

        $application = $this->getLatestEligibleApplicationForCast($castId);

        if (!$application) {
            return ['success' => false, 'message' => '入金申請の対象となる採用済み案件がありません。'];
        }

        $existingDeposit = DB::table('application_deposits')
            ->where('shop_job_application_id', $application->id)
            ->orderByDesc('id')
            ->first();

        if ($existingDeposit) {
            return ['success' => false, 'message' => 'この案件の入金申請はすでに登録済みです。'];
        }

        if (empty($payload['confirm_bonus_condition'])) {
            return ['success' => false, 'message' => 'ボーナス金達成条件を確認したうえで申請してください。'];
        }

        $existingReview = $this->findExistingReviewForApplication($application);
        if (!$existingReview) {
            $reviewValidation = $this->validateReviewPayload($payload);
            if (!$reviewValidation['success']) {
                return $reviewValidation;
            }

            $this->createReviewForApplication($application, $payload);
        }

        $amounts = $this->calculateAmounts($application);
        $depositId = DB::table('application_deposits')->insertGetId($this->filterExistingColumns('application_deposits', [
            'shop_job_application_id' => $application->id,
            'status' => self::STATUS_CAST_REQUESTED,
            'is_read' => false,
            'bonus_amount' => $amounts['bonus_amount'],
            'system_fee_amount' => $amounts['system_fee_amount'],
            'invoice_amount' => $amounts['invoice_amount'],
            'cast_transfer_amount' => $amounts['cast_transfer_amount'],
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        $this->appendHistory($depositId, self::STATUS_CAST_REQUESTED);

        return ['success' => true, 'message' => '入金申請を受け付けました。店舗・運営の確認をお待ちください。'];
    }

    public function confirmDepositForShop(string $shopId, array $payload = []): array
    {
        $deposit = $this->findLatestDepositForShop($shopId);

        if (!$deposit || (int) $deposit->status !== self::STATUS_CAST_REQUESTED) {
            return ['success' => false, 'message' => '店舗確認待ちの入金申請がありません。'];
        }

        if (empty($payload['confirm_bonus_condition'])) {
            return ['success' => false, 'message' => '求人に登録したボーナス達成条件を確認してから承認してください。'];
        }

        if (empty($payload['confirm_review_checked'])) {
            return ['success' => false, 'message' => 'キャストのレビュー内容を確認してから承認してください。'];
        }

        if (!$this->findLatestReviewForCastShop((string) $deposit->cast_id, (string) $deposit->shop_id)) {
            return ['success' => false, 'message' => 'キャストのレビューが見つかりません。内容を確認してから再度お試しください。'];
        }

        DB::table('application_deposits')
            ->where('id', $deposit->id)
            ->update($this->filterExistingColumns('application_deposits', [
                'status' => self::STATUS_SHOP_APPROVED,
                'updated_at' => now(),
            ]));

        $this->appendHistory((int) $deposit->id, self::STATUS_SHOP_APPROVED);

        return ['success' => true, 'message' => 'ノルマ達成・店舗審査を完了しました。運営による請求書発行をお待ちください。'];
    }

    public function issueInvoice(int $depositId, array $payload = []): array
    {
        $deposit = $this->findDepositById($depositId);
        $adminBank = $this->getAdminBankAccount();

        if (!$deposit) {
            return ['success' => false, 'message' => '対象データが見つかりません。'];
        }

        if ((int) $deposit->status !== self::STATUS_SHOP_APPROVED) {
            return ['success' => false, 'message' => 'この請求は現在のステータスでは発行できません。'];
        }

        if (!$adminBank) {
            return ['success' => false, 'message' => '先に運営口座を登録してください。'];
        }

        if (empty($payload['confirm_shop_approved']) || empty($payload['confirm_admin_bank_ready'])) {
            return ['success' => false, 'message' => '請求書発行前の確認項目を完了してください。'];
        }

        $amounts = $this->calculateAmounts($deposit);
        $issuedAt = now();
        $invoiceNumber = $deposit->invoice_number ?: $this->generateInvoiceNumber((int) $deposit->id, $issuedAt);

        DB::table('application_deposits')
            ->where('id', $depositId)
            ->update($this->filterExistingColumns('application_deposits', [
                'status' => self::STATUS_INVOICE_ISSUED,
                'invoice_number' => $invoiceNumber,
                'bonus_amount' => $amounts['bonus_amount'],
                'system_fee_amount' => $amounts['system_fee_amount'],
                'invoice_amount' => $amounts['invoice_amount'],
                'cast_transfer_amount' => $amounts['cast_transfer_amount'],
                'invoice_issued_at' => $issuedAt,
                'invoice_due_date' => $issuedAt->copy()->addDays(self::INVOICE_DUE_DAYS)->toDateString(),
                'invoice_sent_at' => $issuedAt,
                'updated_at' => $issuedAt,
            ]));

        $this->appendHistory($depositId, self::STATUS_INVOICE_ISSUED);

        $mailSent = false;
        if (!empty($deposit->shop_email)) {
            $mailSent = $this->sendInvoiceMail($depositId, $deposit->shop_email);
        }

        return [
            'success' => true,
            'message' => $mailSent
                ? '請求書を発行し、店舗へ送付しました。'
                : '請求書を発行しました。メール送付は現在のメール設定に依存するため、店舗画面からも確認できるようにしています。',
        ];
    }

    public function reportShopPayment(string $shopId, array $payload): array
    {
        $deposit = $this->findLatestDepositForShop($shopId);

        if (!$deposit || !in_array((int) $deposit->status, [self::STATUS_INVOICE_ISSUED, self::STATUS_SHOP_PAYMENT_REPORTED], true)) {
            return ['success' => false, 'message' => '入金報告できる請求書がありません。'];
        }

        $reportedAt = Carbon::parse($payload['reported_at']);

        DB::table('application_deposits')
            ->where('id', $deposit->id)
            ->update($this->filterExistingColumns('application_deposits', [
                'status' => self::STATUS_SHOP_PAYMENT_REPORTED,
                'shop_payment_reported_at' => $reportedAt,
                'shop_payment_reported_amount' => (int) $payload['reported_amount'],
                'shop_payment_reference' => $payload['reference'] ?? null,
                'updated_at' => now(),
            ]));

        if ((int) $deposit->status === self::STATUS_INVOICE_ISSUED) {
            $this->appendHistory((int) $deposit->id, self::STATUS_SHOP_PAYMENT_REPORTED);
        }

        return ['success' => true, 'message' => '入金報告を受け付けました。運営による着金照合をお待ちください。'];
    }

    public function confirmShopPayment(int $depositId, array $payload): array
    {
        $deposit = $this->findDepositById($depositId);

        if (!$deposit) {
            return ['success' => false, 'message' => '対象データが見つかりません。'];
        }

        if ((int) $deposit->status !== self::STATUS_SHOP_PAYMENT_REPORTED) {
            return ['success' => false, 'message' => 'この入金報告は現在のステータスでは照合できません。'];
        }

        if (
            empty($payload['confirm_amount_checked'])
            || empty($payload['confirm_report_checked'])
            || empty($payload['confirm_bank_checked'])
        ) {
            return ['success' => false, 'message' => '店舗入金照合前の確認項目を完了してください。'];
        }

        $confirmedAmount = (int) $payload['confirmed_amount'];
        $expectedAmount = (int) ($deposit->invoice_amount ?? 0);
        $reportedAmount = (int) ($deposit->shop_payment_reported_amount ?? 0);

        if ($confirmedAmount !== $expectedAmount || $reportedAmount !== $expectedAmount) {
            return ['success' => false, 'message' => '請求金額と報告金額が一致しません。店舗へ再確認してください。'];
        }

        DB::table('application_deposits')
            ->where('id', $depositId)
            ->update($this->filterExistingColumns('application_deposits', [
                'status' => self::STATUS_SHOP_PAYMENT_CONFIRMED,
                'shop_payment_confirmed_at' => now(),
                'updated_at' => now(),
            ]));

        $this->appendHistory($depositId, self::STATUS_SHOP_PAYMENT_CONFIRMED);

        return ['success' => true, 'message' => '店舗からの入金を確認しました。キャストへの振込準備に進めます。'];
    }

    public function executeCastTransfer(int $depositId, array $payload): array
    {
        $deposit = $this->findDepositById($depositId);

        if (!$deposit) {
            return ['success' => false, 'message' => '対象データが見つかりません。'];
        }

        if ((int) $deposit->status !== self::STATUS_SHOP_PAYMENT_CONFIRMED) {
            return ['success' => false, 'message' => 'このデータはまだキャスト振込へ進めません。'];
        }

        if (!$this->getCastBankAccount((string) $deposit->cast_id)) {
            return ['success' => false, 'message' => 'キャストの口座情報が未登録です。'];
        }

        if (
            empty($payload['confirm_transfer_amount'])
            || empty($payload['confirm_account_name'])
            || empty($payload['confirm_transfer_executed'])
            || empty($payload['confirm_receipt_checked'])
        ) {
            return ['success' => false, 'message' => 'キャスト振込記録前の確認項目を完了してください。'];
        }

        DB::table('application_deposits')
            ->where('id', $depositId)
            ->update($this->filterExistingColumns('application_deposits', [
                'status' => self::STATUS_CAST_TRANSFERRED,
                'cast_transferred_at' => Carbon::parse($payload['transferred_at']),
                'cast_transfer_reference' => $payload['reference'] ?? null,
                'cast_transfer_note' => $payload['note'] ?? null,
                'updated_at' => now(),
            ]));

        $this->appendHistory($depositId, self::STATUS_CAST_TRANSFERRED);

        return ['success' => true, 'message' => 'キャストへの振込手続きを記録しました。キャストの入金確認待ちです。'];
    }

    public function confirmCastReceipt(string $castId): array
    {
        $deposit = $this->findLatestDepositForCast($castId);

        if (!$deposit || (int) $deposit->status !== self::STATUS_CAST_TRANSFERRED) {
            return ['success' => false, 'message' => '確認待ちの振込データがありません。'];
        }

        DB::table('application_deposits')
            ->where('id', $deposit->id)
            ->update($this->filterExistingColumns('application_deposits', [
                'status' => self::STATUS_COMPLETED,
                'completed_at' => now(),
                'updated_at' => now(),
            ]));

        $this->appendHistory((int) $deposit->id, self::STATUS_COMPLETED);

        return ['success' => true, 'message' => '入金確認を記録しました。今回の請求・振込フローは完了です。'];
    }

    public function getShopPaymentPageData(string $shopId): array
    {
        $deposits = collect($this->getAllDeposits())
            ->where('shop_id', $shopId)
            ->values();

        $current = $deposits->first();
        $bank = $this->normalizeBankAccount($this->getShopBankAccount($shopId));
        $approvalTarget = ($current && (int) ($current['status_code'] ?? 0) === self::STATUS_CAST_REQUESTED)
            ? $this->buildShopApprovalTarget($current)
            : null;

        return [
            'current' => $current,
            'flow' => $current['flow'] ?? $this->flowState(0),
            'summary' => [
                'unpaid_total' => $deposits
                    ->whereIn('status_code', [
                        self::STATUS_INVOICE_ISSUED,
                        self::STATUS_SHOP_PAYMENT_REPORTED,
                    ])
                    ->sum('invoice_amount'),
                'next_settlement' => $current['invoice_due_date'] ?? null,
            ],
            'invoices' => $deposits
                ->filter(fn (array $deposit) => !empty($deposit['invoice_number']))
                ->map(fn (array $deposit) => [
                    'id' => $deposit['id'],
                    'title' => '請求書 ' . $deposit['invoice_number'],
                    'amount' => $deposit['invoice_amount'],
                    'status' => in_array($deposit['status_code'], [
                        self::STATUS_SHOP_PAYMENT_CONFIRMED,
                        self::STATUS_CAST_TRANSFERRED,
                        self::STATUS_COMPLETED,
                    ], true) ? 'paid' : 'pending',
                    'date' => $deposit['invoice_issued_at'],
                    'invoice_url' => $this->getSignedInvoiceUrl($deposit['id']),
                ])
                ->values()
                ->all(),
            'bank' => $bank,
            'payment_form' => [
                'reported_amount' => $current['invoice_amount'] ?? '',
                'reported_at' => $current['shop_payment_reported_at_form'] ?? now()->format('Y-m-d\TH:i'),
                'reference' => $current['shop_payment_reference'] ?? '',
            ],
            'approval_target' => $approvalTarget,
            'can_report_payment' => $current && in_array($current['status_code'], [
                self::STATUS_INVOICE_ISSUED,
                self::STATUS_SHOP_PAYMENT_REPORTED,
            ], true),
        ];
    }

    public function getCastPaymentPageData(string $castId): array
    {
        $deposits = collect($this->getAllDeposits())
            ->where('cast_id', $castId)
            ->values();

        $current = $deposits->first();
        $bank = $this->normalizeBankAccount($this->getCastBankAccount($castId));
        $eligibleApplication = $this->getLatestEligibleApplicationForCast($castId);
        $hasExistingDeposit = $eligibleApplication
            ? DB::table('application_deposits')
                ->where('shop_job_application_id', $eligibleApplication->id)
                ->exists()
            : false;
        $requestTarget = $eligibleApplication ? $this->buildCastDepositRequestTarget($eligibleApplication) : null;

        $requestDisabledReason = null;
        if (!$bank['exists']) {
            $requestDisabledReason = '振込先口座を登録すると入金申請できます。';
        } elseif (!$eligibleApplication) {
            $requestDisabledReason = '採用済み案件が確定すると入金申請できます。';
        } elseif ($hasExistingDeposit) {
            $requestDisabledReason = 'この案件の入金申請はすでに登録済みです。';
        }

        return [
            'current' => $current,
            'flow' => $current['flow'] ?? $this->flowState(0),
            'payments' => $deposits->map(fn (array $deposit) => [
                'title' => !empty($deposit['invoice_number'])
                    ? '請求・入金フロー ' . $deposit['invoice_number']
                    : 'ボーナス入金申請',
                'status_label' => $deposit['status_label'],
                'status_class' => in_array($deposit['status_code'], [self::STATUS_CAST_TRANSFERRED, self::STATUS_COMPLETED], true)
                    ? 'status-paid'
                    : 'status-pending',
                'date' => $deposit['updated_at_label'],
                'amount' => $deposit['cast_transfer_amount'],
                'link' => !empty($deposit['invoice_number']) ? $this->getSignedInvoiceUrl($deposit['id']) : null,
            ])->all(),
            'bank' => $bank,
            'can_request' => $requestDisabledReason === null,
            'request_disabled_reason' => $requestDisabledReason,
            'request_target' => $requestTarget,
        ];
    }

    public function getInvoiceData(int $depositId): ?array
    {
        $deposit = $this->findDepositById($depositId);
        $adminBank = $this->getAdminBankAccount();

        if (!$deposit || !$adminBank) {
            return null;
        }

        $amounts = $this->calculateAmounts($deposit);
        $issuedAt = $deposit->invoice_issued_at ? Carbon::parse($deposit->invoice_issued_at) : now();
        $dueDate = $deposit->invoice_due_date
            ? Carbon::parse($deposit->invoice_due_date)
            : $issuedAt->copy()->addDays(self::INVOICE_DUE_DAYS);

        return [
            'deposit_id' => (int) $deposit->id,
            'invoice_number' => $deposit->invoice_number ?: $this->generateInvoiceNumber((int) $deposit->id, $issuedAt),
            'issued_at' => $issuedAt,
            'due_date' => $dueDate,
            'shop_name' => $deposit->shop_name,
            'shop_email' => $deposit->shop_email,
            'shop_address' => trim(implode(' ', array_filter([
                $deposit->shop_pref,
                $deposit->shop_city,
                $deposit->shop_addr2,
                $deposit->shop_addr3,
            ]))),
            'cast_name' => $this->castName($deposit),
            'bonus_amount' => $amounts['bonus_amount'],
            'system_fee_amount' => $amounts['system_fee_amount'],
            'invoice_amount' => $amounts['invoice_amount'],
            'cast_transfer_amount' => $amounts['cast_transfer_amount'],
            'admin_bank' => [
                'bank_name' => $adminBank->bank_name,
                'branch_name' => $adminBank->branch_name,
                'account_type_label' => $this->accountTypeLabel($adminBank->account_type),
                'account_number' => $adminBank->account_number,
                'account_holder_name' => $adminBank->account_holder_name ?? '',
                'account_name' => $adminBank->account_name,
            ],
        ];
    }

    public function getSignedInvoiceUrl(int $depositId, ?Carbon $expiresAt = null): string
    {
        return URL::temporarySignedRoute(
            'billing.invoices.show',
            $expiresAt ?: now()->addDays(30),
            ['deposit' => $depositId]
        );
    }

    public function statusLabel(int $status): string
    {
        return match ($status) {
            self::STATUS_CAST_REQUESTED => '申請中',
            self::STATUS_SHOP_APPROVED => '請求待ち',
            self::STATUS_INVOICE_ISSUED => '店舗へ請求中',
            self::STATUS_SHOP_PAYMENT_REPORTED => '店舗入金確認中',
            self::STATUS_SHOP_PAYMENT_CONFIRMED => '店舗入金確認済',
            self::STATUS_CAST_TRANSFERRED => 'キャスト振込済',
            self::STATUS_COMPLETED => '完了',
            default => '未申請',
        };
    }

    public function flowState(int $status): array
    {
        return match ($status) {
            self::STATUS_CAST_REQUESTED => ['cast' => '申請中', 'shop' => '承認待ち', 'admin' => '提出待ち'],
            self::STATUS_SHOP_APPROVED => ['cast' => '申請中', 'shop' => '請求待ち', 'admin' => '店舗承認済'],
            self::STATUS_INVOICE_ISSUED => ['cast' => 'お振込準備中', 'shop' => 'お支払い待ち', 'admin' => '店舗へ請求中'],
            self::STATUS_SHOP_PAYMENT_REPORTED => ['cast' => 'お振込準備中', 'shop' => '入金報告済', 'admin' => '店舗入金確認中'],
            self::STATUS_SHOP_PAYMENT_CONFIRMED => ['cast' => 'お振込準備中', 'shop' => 'お支払い完了', 'admin' => '店舗入金確認済'],
            self::STATUS_CAST_TRANSFERRED => ['cast' => 'お振込手続き中', 'shop' => 'お支払い完了', 'admin' => 'キャスト振込済'],
            self::STATUS_COMPLETED => ['cast' => '完了', 'shop' => '完了', 'admin' => '完了'],
            default => ['cast' => '未申請', 'shop' => '未稼働', 'admin' => '未稼働'],
        };
    }

    public function getAllDeposits(): array
    {
        return $this->baseDepositQuery()
            ->orderByDesc('application_deposits.id')
            ->get()
            ->map(fn (object $row) => $this->mapDepositRow($row))
            ->all();
    }

    private function baseDepositQuery()
    {
        return DB::table('application_deposits')
            ->join('shop_job_applications', 'application_deposits.shop_job_application_id', '=', 'shop_job_applications.id')
            ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->join('shops', 'shop_jobs.shop_id', '=', 'shops.id')
            ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->join('casts', 'shop_job_applications.cast_id', '=', 'casts.id')
            ->leftJoin('cast_profiles', 'casts.id', '=', 'cast_profiles.cast_id')
            ->leftJoin('bank_accounts', 'casts.id', '=', 'bank_accounts.member_id')
            ->leftJoin('bank_account_shops', 'shops.id', '=', 'bank_account_shops.shop_id')
            ->select(
                'application_deposits.id',
                'application_deposits.shop_job_application_id',
                'application_deposits.status',
                'application_deposits.is_read',
                'application_deposits.created_at',
                'application_deposits.updated_at',
                ...$this->optionalDepositSelects(),
                'shop_job_applications.id as application_id',
                'shop_job_applications.cast_id',
                'shop_job_applications.result_date',
                'shop_jobs.shop_id',
                'shop_jobs.hourly_wage_regular',
                'shop_jobs.noruma_reward',
                'shop_jobs.noruma_cond',
                'shops.email as shop_email',
                'shop_profiles.shop_name',
                'shop_profiles.pref as shop_pref',
                'shop_profiles.city as shop_city',
                'shop_profiles.addr2 as shop_addr2',
                'shop_profiles.addr3 as shop_addr3',
                'cast_profiles.nickname as cast_nickname',
                'cast_profiles.name as cast_full_name',
                'casts.email as cast_email',
                DB::raw('bank_accounts.id as cast_bank_id'),
                DB::raw('bank_account_shops.id as shop_bank_id')
            );
    }

    private function findDepositById(int $depositId): ?object
    {
        return $this->baseDepositQuery()
            ->where('application_deposits.id', $depositId)
            ->first();
    }

    private function findLatestDepositForShop(string $shopId): ?object
    {
        return $this->baseDepositQuery()
            ->where('shops.id', $shopId)
            ->orderByDesc('application_deposits.id')
            ->first();
    }

    private function findLatestDepositForCast(string $castId): ?object
    {
        return $this->baseDepositQuery()
            ->where('casts.id', $castId)
            ->orderByDesc('application_deposits.id')
            ->first();
    }

    private function getLatestEligibleApplicationForCast(string $castId): ?object
    {
        return DB::table('shop_job_applications')
            ->join('shop_jobs', 'shop_job_applications.shop_job_id', '=', 'shop_jobs.id')
            ->join('shops', 'shop_jobs.shop_id', '=', 'shops.id')
            ->leftJoin('shop_profiles', 'shops.id', '=', 'shop_profiles.shop_id')
            ->where('shop_job_applications.cast_id', $castId)
            ->where('shop_job_applications.status', 4)
            ->orderByDesc('shop_job_applications.id')
            ->select(
                'shop_job_applications.*',
                'shop_jobs.shop_id',
                'shop_jobs.noruma_reward',
                'shop_jobs.hourly_wage_regular',
                'shop_jobs.noruma_cond',
                'shop_profiles.shop_name'
            )
            ->first();
    }

    private function buildCastDepositRequestTarget(object $application): array
    {
        $reviewContents = $this->orderedReviewContentsQuery()
            ->get(['id', DB::raw($this->reviewContentColumn() . ' as name')])
            ->map(fn (object $row) => [
                'id' => (int) $row->id,
                'name' => $row->name,
            ])
            ->all();

        $meta = $this->decodeJobMeta($application->noruma_cond ?? null);
        $existingReview = $this->findExistingReviewForApplication($application);

        return [
            'application_id' => (int) $application->id,
            'shop_id' => $application->shop_id,
            'shop_name' => $application->shop_name ?: $application->shop_id,
            'bonus_amount' => (int) ($application->noruma_reward ?? $application->hourly_wage_regular ?? 0),
            'bonus_condition' => trim((string) ($meta['bonus_condition'] ?? '')),
            'review_contents' => $reviewContents,
            'review_exists' => $existingReview !== null,
            'review_comment' => $existingReview->contents ?? '',
            'review_posted_at' => !empty($existingReview->created_at)
                ? Carbon::parse($existingReview->created_at)->format('Y-m-d H:i')
                : null,
        ];
    }

    private function findExistingReviewForApplication(object $application): ?object
    {
        return $this->findLatestReviewForCastShop((string) $application->cast_id, (string) $application->shop_id);
    }

    private function findLatestReviewForCastShop(string $castId, string $shopId): ?object
    {
        return DB::table('reviews')
            ->where('cast_id', $castId)
            ->where('shop_id', $shopId)
            ->orderByDesc('id')
            ->first();
    }

    private function buildShopApprovalTarget(array $deposit): array
    {
        $review = $this->findLatestReviewForCastShop((string) $deposit['cast_id'], (string) $deposit['shop_id']);
        $reviewDetails = [];

        if ($review) {
            $reviewDetails = $this->orderReviewContentJoin(
                DB::table('review_details')
                ->join('review_contents', 'review_details.' . $this->reviewDetailContentColumn(), '=', 'review_contents.id')
                ->where('review_details.review_id', $review->id)
            )
                ->get([
                    DB::raw('review_contents.' . $this->reviewContentColumn() . ' as name'),
                    'review_details.score',
                ])
                ->map(fn (object $row) => [
                    'name' => $row->name,
                    'score' => (float) $row->score,
                ])
                ->all();
        }

        $meta = $this->decodeJobMeta($deposit['noruma_cond'] ?? null);

        return [
            'application_id' => $deposit['application_id'],
            'cast_name' => $deposit['cast_name'],
            'bonus_amount' => (int) ($deposit['bonus_amount'] ?? 0),
            'bonus_condition' => trim((string) ($meta['bonus_condition'] ?? '')),
            'requested_at' => $deposit['updated_at_label'] ?? null,
            'review_comment' => $review->contents ?? '',
            'review_average' => isset($review->eva) ? (float) $review->eva : null,
            'review_posted_at' => !empty($review->created_at)
                ? Carbon::parse($review->created_at)->format('Y-m-d H:i')
                : null,
            'review_details' => $reviewDetails,
        ];
    }

    private function validateReviewPayload(array $payload): array
    {
        $comment = trim((string) ($payload['review_comment'] ?? ''));
        $requiredContentIds = $this->orderedReviewContentsQuery()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $scores = collect($payload['review_scores'] ?? [])
            ->mapWithKeys(fn ($score, $contentId) => [(int) $contentId => (int) $score])
            ->filter(fn ($score, $contentId) => $contentId > 0 && $score >= 1 && $score <= 5);

        if ($comment === '') {
            return ['success' => false, 'message' => '入金申請の前にレビューコメントを入力してください。'];
        }

        if (empty($requiredContentIds)) {
            return ['success' => false, 'message' => 'レビュー設問マスタが未設定です。管理者にお問い合わせください。'];
        }

        foreach ($requiredContentIds as $contentId) {
            if (!$scores->has($contentId)) {
                return ['success' => false, 'message' => 'レビュー評価をすべて入力してから申請してください。'];
            }
        }

        return ['success' => true];
    }

    private function createReviewForApplication(object $application, array $payload): void
    {
        $scores = collect($payload['review_scores'] ?? [])
            ->mapWithKeys(fn ($score, $contentId) => [(int) $contentId => (int) $score])
            ->filter(fn ($score, $contentId) => $contentId > 0 && $score >= 1 && $score <= 5);

        $reviewId = DB::table('reviews')->insertGetId([
            'cast_id' => $application->cast_id,
            'shop_id' => $application->shop_id,
            'contents' => trim((string) ($payload['review_comment'] ?? '')),
            'eva' => round($scores->avg() ?? 0, 1),
            'created_at' => now(),
        ]);

        $detailRows = $scores->map(fn ($score, $contentId) => [
            'review_id' => $reviewId,
            $this->reviewDetailContentColumn() => (int) $contentId,
            'score' => $score,
        ])->values()->all();

        if (!empty($detailRows)) {
            DB::table('review_details')->insert($detailRows);
        }
    }

    private function mapDepositRow(object $row): array
    {
        $amounts = $this->calculateAmounts($row);
        $status = (int) $row->status;
        $meta = $this->decodeJobMeta($row->noruma_cond ?? null);
        $review = $this->findLatestReviewForCastShop((string) $row->cast_id, (string) $row->shop_id);
        $reviewDetails = [];

        if ($review) {
            $reviewDetails = $this->orderReviewContentJoin(
                DB::table('review_details')
                ->join('review_contents', 'review_details.' . $this->reviewDetailContentColumn(), '=', 'review_contents.id')
                ->where('review_details.review_id', $review->id)
            )
                ->get([
                    DB::raw('review_contents.' . $this->reviewContentColumn() . ' as name'),
                    'review_details.score',
                ])
                ->map(fn (object $detail) => [
                    'name' => $detail->name,
                    'score' => (float) $detail->score,
                ])
                ->all();
        }

        return [
            'id' => (int) $row->id,
            'status_code' => $status,
            'status_label' => $this->statusLabel($status),
            'shop_id' => $row->shop_id,
            'shop_name' => $row->shop_name ?: $row->shop_id,
            'shop_email' => $row->shop_email,
            'cast_id' => $row->cast_id,
            'cast_name' => $this->castName($row),
            'cast_email' => $row->cast_email,
            'invoice_number' => $row->invoice_number,
            'bonus_amount' => $amounts['bonus_amount'],
            'system_fee_amount' => $amounts['system_fee_amount'],
            'invoice_amount' => $amounts['invoice_amount'],
            'cast_transfer_amount' => $amounts['cast_transfer_amount'],
            'invoice_issued_at' => $this->formatDateTime($row->invoice_issued_at),
            'invoice_due_date' => $row->invoice_due_date ? Carbon::parse($row->invoice_due_date)->format('Y-m-d') : null,
            'shop_payment_reported_at' => $this->formatDateTime($row->shop_payment_reported_at),
            'shop_payment_reported_at_form' => $row->shop_payment_reported_at
                ? Carbon::parse($row->shop_payment_reported_at)->format('Y-m-d\TH:i')
                : null,
            'shop_payment_reported_amount' => (int) ($row->shop_payment_reported_amount ?? 0),
            'shop_payment_reference' => $row->shop_payment_reference,
            'shop_payment_confirmed_at' => $this->formatDateTime($row->shop_payment_confirmed_at),
            'cast_transferred_at' => $this->formatDateTime($row->cast_transferred_at),
            'cast_transfer_reference' => $row->cast_transfer_reference,
            'cast_transfer_note' => $row->cast_transfer_note,
            'completed_at' => $this->formatDateTime($row->completed_at),
            'flow' => $this->flowState($status),
            'next_action' => $this->nextActionLabel($status),
            'has_cast_bank' => !empty($row->cast_bank_id),
            'has_shop_bank' => !empty($row->shop_bank_id),
            'updated_at_label' => $this->formatDateTime($row->updated_at),
            'noruma_cond' => $row->noruma_cond ?? null,
            'bonus_condition' => trim((string) ($meta['bonus_condition'] ?? '')),
            'review_comment' => $review->contents ?? '',
            'review_average' => isset($review->eva) ? (float) $review->eva : null,
            'review_posted_at' => !empty($review->created_at)
                ? Carbon::parse($review->created_at)->format('Y-m-d H:i')
                : null,
            'review_details' => $reviewDetails,
        ];
    }

    private function calculateAmounts(object $row): array
    {
        $bonusAmount = (int) ($row->bonus_amount
            ?? $row->noruma_reward
            ?? $row->hourly_wage_regular
            ?? 0);

        $systemFeeAmount = (int) ($row->system_fee_amount ?? round($bonusAmount * self::SYSTEM_FEE_RATE));
        $invoiceAmount = (int) ($row->invoice_amount ?? ($bonusAmount + $systemFeeAmount));
        $castTransferAmount = (int) ($row->cast_transfer_amount ?? $bonusAmount);

        return [
            'bonus_amount' => $bonusAmount,
            'system_fee_amount' => $systemFeeAmount,
            'invoice_amount' => $invoiceAmount,
            'cast_transfer_amount' => $castTransferAmount,
        ];
    }

    private function appendHistory(int $depositId, int $status): void
    {
        DB::table('application_deposit_histories')->insert([
            'application_deposit_id' => $depositId,
            'status' => $status,
            'status_date' => now(),
            'created_at' => now(),
        ]);
    }

    private function sendInvoiceMail(int $depositId, string $shopEmail): bool
    {
        $invoice = $this->getInvoiceData($depositId);

        if (!$invoice || !view()->exists('emails.shop-invoice-issued')) {
            return false;
        }

        try {
            Mail::send('emails.shop-invoice-issued', [
                'invoice' => $invoice,
                'invoiceUrl' => $this->getSignedInvoiceUrl($depositId),
            ], function ($message) use ($shopEmail, $invoice) {
                $message->to($shopEmail)
                    ->subject('【ミセチョク】請求書を発行しました: ' . $invoice['invoice_number']);
            });

            return true;
        } catch (\Throwable $e) {
            Log::warning('Failed to send invoice mail.', [
                'deposit_id' => $depositId,
                'shop_email' => $shopEmail,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function generateInvoiceNumber(int $depositId, Carbon $issuedAt): string
    {
        return sprintf('INV-%s-%04d', $issuedAt->format('Ym'), $depositId);
    }

    private function castName(object $deposit): string
    {
        return $deposit->cast_nickname ?: ($deposit->cast_full_name ?: $deposit->cast_id);
    }

    private function nextActionLabel(int $status): string
    {
        return match ($status) {
            self::STATUS_CAST_REQUESTED => '店舗承認待ち',
            self::STATUS_SHOP_APPROVED => '請求書発行',
            self::STATUS_INVOICE_ISSUED => '店舗の入金報告待ち',
            self::STATUS_SHOP_PAYMENT_REPORTED => '着金照合',
            self::STATUS_SHOP_PAYMENT_CONFIRMED => 'キャスト振込',
            self::STATUS_CAST_TRANSFERRED => 'キャスト確認待ち',
            self::STATUS_COMPLETED => '完了',
            default => '未着手',
        };
    }

    private function normalizeBankAccount(?object $bank): array
    {
        $accountHolderName = $bank->account_holder_name ?? $bank->account_name ?? '';

        return [
            'exists' => $bank !== null,
            'bank_name' => $bank->bank_name ?? '',
            'branch_name' => $bank->branch_name ?? '',
            'account_type' => $bank->account_type ?? 'ordinary',
            'account_number' => $bank->account_number ?? '',
            'account_holder_name' => $accountHolderName,
            'account_name' => $bank->account_name ?? '',
            'account_type_label' => $this->accountTypeLabel($bank->account_type ?? 'ordinary'),
        ];
    }

    private function accountTypeLabel(string $accountType): string
    {
        return match ($accountType) {
            'checking' => '当座',
            default => '普通',
        };
    }

    private function formatDateTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d H:i');
    }

    private function decodeJobMeta(?string $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function orderedReviewContentsQuery()
    {
        $query = DB::table('review_contents');

        if (Schema::hasTable('review_contents') && Schema::hasColumn('review_contents', 'del_flg')) {
            $query->where('del_flg', 0);
        } elseif (Schema::hasTable('review_contents') && Schema::hasColumn('review_contents', 'is_active')) {
            $query->where('is_active', true);
        }

        if (Schema::hasTable('review_contents') && Schema::hasColumn('review_contents', 'sort_order')) {
            $query->orderBy('sort_order');
        }

        return $query->orderBy('id');
    }

    private function orderReviewContentJoin($query)
    {
        if (Schema::hasTable('review_contents') && Schema::hasColumn('review_contents', 'sort_order')) {
            $query->orderBy('review_contents.sort_order');
        }

        return $query->orderBy('review_contents.id');
    }

    private function reviewContentColumn(): string
    {
        return Schema::hasTable('review_contents') && Schema::hasColumn('review_contents', 'content')
            ? 'content'
            : 'name';
    }

    private function reviewDetailContentColumn(): string
    {
        if (Schema::hasTable('review_details') && Schema::hasColumn('review_details', 'val')) {
            return 'val';
        }

        return 'review_content_id';
    }

    private function filterExistingColumns(string $table, array $payload): array
    {
        if (!Schema::hasTable($table)) {
            return $payload;
        }

        return collect($payload)
            ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
            ->all();
    }

    private function bankAccountSelectColumns(string $table): array
    {
        $columns = [
            'id',
            'bank_name',
            'branch_name',
            'account_type',
            'account_number',
            'account_name',
        ];

        if (Schema::hasTable($table) && Schema::hasColumn($table, 'account_holder_name')) {
            $columns[] = 'account_holder_name';
        } else {
            $columns[] = DB::raw('account_name as account_holder_name');
        }

        if (Schema::hasTable($table) && Schema::hasColumn($table, 'member_id')) {
            $columns[] = 'member_id';
        }

        if (Schema::hasTable($table) && Schema::hasColumn($table, 'shop_id')) {
            $columns[] = 'shop_id';
        }

        if (Schema::hasTable($table) && Schema::hasColumn($table, 'is_active')) {
            $columns[] = 'is_active';
        }

        return $columns;
    }

    private function normalizeBankAccountRecord(?object $account): ?object
    {
        if (!$account) {
            return null;
        }

        $account->account_holder_name = $account->account_holder_name ?? $account->account_name ?? '';

        return $account;
    }

    private function optionalDepositSelects(): array
    {
        $optionalColumns = [
            'invoice_number',
            'bonus_amount',
            'system_fee_amount',
            'invoice_amount',
            'cast_transfer_amount',
            'invoice_issued_at',
            'invoice_due_date',
            'invoice_sent_at',
            'shop_payment_reported_at',
            'shop_payment_reported_amount',
            'shop_payment_reference',
            'shop_payment_confirmed_at',
            'cast_transferred_at',
            'cast_transfer_reference',
            'cast_transfer_note',
            'completed_at',
        ];

        return array_map(function (string $column) {
            if (Schema::hasTable('application_deposits') && Schema::hasColumn('application_deposits', $column)) {
                return 'application_deposits.' . $column;
            }

            return DB::raw('NULL as ' . $column);
        }, $optionalColumns);
    }
}
