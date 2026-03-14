<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\SystemAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = SystemAccount::query()
            ->where('email', 'admin@misechoku.jp')
            ->value('id');

        if ($adminId) {
            $this->upsertBankAccount([
                'holder_type' => BankAccount::HOLDER_SYSTEM_ACCOUNT,
                'holder_id' => (string) $adminId,
                'bank_code' => '0001',
                'bank_name' => 'みせちょく銀行',
                'bank_name_kana' => 'ﾐｾﾁｮｸ',
                'branch_code' => '001',
                'branch_name' => '本店営業部',
                'branch_name_kana' => 'ﾎﾝﾃﾝ',
                'account_type' => 'ordinary',
                'account_number' => '1234567',
                'account_name' => 'ﾐｾﾁｮｸｳﾝｴｲ',
            ]);
        }

        $demoAccounts = [
            [
                'holder_type' => BankAccount::HOLDER_SHOP,
                'holder_id' => 's00000001',
                'bank_code' => '0002',
                'bank_name' => '六本木銀行',
                'bank_name_kana' => 'ﾛｯﾎﾟﾝｷﾞ',
                'branch_code' => '101',
                'branch_name' => '六本木支店',
                'branch_name_kana' => 'ﾛｯﾎﾟﾝｷﾞ',
                'account_type' => 'ordinary',
                'account_number' => '7654321',
                'account_name' => 'ｸﾗﾌﾞﾙﾐﾅｽ',
            ],
            [
                'holder_type' => BankAccount::HOLDER_SHOP,
                'holder_id' => 's00000002',
                'bank_code' => '0003',
                'bank_name' => '新宿銀行',
                'bank_name_kana' => 'ｼﾝｼﾞｭｸ',
                'branch_code' => '102',
                'branch_name' => '歌舞伎町支店',
                'branch_name_kana' => 'ｶﾌﾞｷﾁｮｳ',
                'account_type' => 'ordinary',
                'account_number' => '1122334',
                'account_name' => 'ﾗｳﾝｼﾞｽﾃﾗ',
            ],
            [
                'holder_type' => BankAccount::HOLDER_CAST,
                'holder_id' => 'c00000001',
                'bank_code' => '0004',
                'bank_name' => '渋谷銀行',
                'bank_name_kana' => 'ｼﾌﾞﾔ',
                'branch_code' => '201',
                'branch_name' => '青山支店',
                'branch_name_kana' => 'ｱｵﾔﾏ',
                'account_type' => 'ordinary',
                'account_number' => '2200113',
                'account_name' => 'ｻｸﾗｲﾐｻｷ',
            ],
            [
                'holder_type' => BankAccount::HOLDER_CAST,
                'holder_id' => 'c00000002',
                'bank_code' => '0005',
                'bank_name' => '横浜銀行',
                'bank_name_kana' => 'ﾖｺﾊﾏ',
                'branch_code' => '202',
                'branch_name' => '横浜中央支店',
                'branch_name_kana' => 'ﾖｺﾊﾏﾁｭｳｵｳ',
                'account_type' => 'ordinary',
                'account_number' => '3344556',
                'account_name' => 'ﾔﾏﾀﾞｱｲ',
            ],
        ];

        foreach ($demoAccounts as $account) {
            $this->upsertBankAccount($account);
        }
    }

    private function upsertBankAccount(array $payload): void
    {
        DB::table('bank_accounts')->updateOrInsert(
            [
                'holder_type' => $payload['holder_type'],
                'holder_id' => $payload['holder_id'],
            ],
            array_merge($payload, [
                'updated_at' => now(),
                'created_at' => now(),
            ])
        );
    }
}
