<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BankLookupService
{
    private const BANKS_CACHE_KEY = 'bank_lookup.banks';
    private const BANKS_URL = 'https://bank.teraren.com/banks.json';
    private const BRANCHES_URL_TEMPLATE = 'https://bank.teraren.com/banks/%s/branches.json';

    public function searchBanks(string $query, int $limit = 20): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        return $this->banks()
            ->filter(fn (array $bank) => $this->matchesQuery($bank, $query))
            ->take($limit)
            ->values()
            ->all();
    }

    public function searchBranches(string $bankCode, string $query = '', int $limit = 30): array
    {
        $bankCode = trim($bankCode);

        if (!preg_match('/^\d{4}$/', $bankCode)) {
            return [];
        }

        $query = trim($query);

        return $this->branches($bankCode)
            ->when($query !== '', fn (Collection $branches) => $branches->filter(
                fn (array $branch) => $this->matchesQuery($branch, $query)
            ))
            ->take($limit)
            ->values()
            ->all();
    }

    private function banks(): Collection
    {
        return collect(Cache::remember(self::BANKS_CACHE_KEY, now()->addDay(), function () {
            return $this->fetchJson(self::BANKS_URL)
                ->map(fn (array $item) => $this->mapBank($item))
                ->all();
        }));
    }

    private function branches(string $bankCode): Collection
    {
        return collect(Cache::remember(
            'bank_lookup.branches.' . $bankCode,
            now()->addDay(),
            function () use ($bankCode) {
                $url = sprintf(self::BRANCHES_URL_TEMPLATE, $bankCode);

                return $this->fetchJson($url)
                    ->map(fn (array $item) => $this->mapBranch($item, $bankCode))
                    ->all();
            }
        ));
    }

    private function fetchJson(string $url): Collection
    {
        $response = Http::timeout(5)->acceptJson()->get($url);

        if (!$response->successful()) {
            return collect();
        }

        $payload = $response->json();

        return is_array($payload) ? collect($payload) : collect();
    }

    private function mapBank(array $item): array
    {
        $displayName = trim((string) data_get($item, 'normalize.name', data_get($item, 'name', '')));

        return [
            'code' => (string) ($item['code'] ?? ''),
            'name' => $displayName,
            'short_name' => (string) ($item['name'] ?? ''),
            'kana' => (string) ($item['kana'] ?? data_get($item, 'normalize.kana', '')),
            'hira' => (string) ($item['hira'] ?? data_get($item, 'normalize.hira', '')),
        ];
    }

    private function mapBranch(array $item, string $bankCode): array
    {
        $displayName = trim((string) data_get($item, 'normalize.name', data_get($item, 'name', '')));

        return [
            'bank_code' => $bankCode,
            'code' => (string) ($item['code'] ?? ''),
            'name' => $displayName,
            'short_name' => (string) ($item['name'] ?? ''),
            'kana' => (string) ($item['kana'] ?? data_get($item, 'normalize.kana', '')),
            'hira' => (string) ($item['hira'] ?? data_get($item, 'normalize.hira', '')),
        ];
    }

    private function matchesQuery(array $item, string $query): bool
    {
        $needle = mb_strtolower($query);

        foreach (['code', 'name', 'short_name', 'kana', 'hira'] as $field) {
            $value = trim((string) ($item[$field] ?? ''));

            if ($value !== '' && mb_strpos(mb_strtolower($value), $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
