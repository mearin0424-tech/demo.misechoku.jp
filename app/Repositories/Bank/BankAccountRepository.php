<?php
namespace App\Repositories\Bank;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Lib\StrUtil;
use App\Lib\FileUtil;
use Illuminate\Http\Request;
use App\Consts\ShopConsts;


class BankAccountRepository implements BankAccountRepositoryInterface
{
    /**
     * @var App\Models\Project
     */
    private $project;

    public function __construct(BankAccount $project) {
        $this->project = $project;
    }


     /**
     * 口座情報を登録する
     *
     * @param array $data
     * @return Project
     */
    public function store(Request $request,$member_id)
    {
        $this->storeForHolder(BankAccount::HOLDER_CAST, $member_id, $request);
    }

    public function findBankAccountByMemberId($member_id)
    {
        return $this->findBankAccount(BankAccount::HOLDER_CAST, $member_id);
    }


    public function storeByShopId(Request $request,$shop_id)
    {
        $this->storeForHolder(BankAccount::HOLDER_SHOP, $shop_id, $request);
    }

    public function findBankAccountByShopId($shop_id)
    {
        return $this->findBankAccount(BankAccount::HOLDER_SHOP, $shop_id);
    }

    private function filterPayload(string $table, array $payload): array
    {
        return collect($payload)
            ->only([
                'bank_code',
                'bank_name',
                'bank_name_kana',
                'branch_code',
                'branch_name',
                'branch_name_kana',
                'account_type',
                'account_number',
                'account_name',
            ])
            ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
            ->all();
    }

    private function storeForHolder(string $holderType, string $holderId, Request $request): void
    {
        BankAccount::updateOrCreate(
            [
                'holder_type' => $holderType,
                'holder_id' => $holderId,
            ],
            $this->filterPayload('bank_accounts', $request->all())
        );
    }

    private function findBankAccount(string $holderType, string $holderId): ?object
    {
        return DB::table('bank_accounts')
            ->where('holder_type', $holderType)
            ->where('holder_id', $holderId)
            ->first();
    }


}
