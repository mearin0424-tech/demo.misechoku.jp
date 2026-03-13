<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Services\BankLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankLookupController extends Controller
{
    public function __construct(private readonly BankLookupService $bankLookupService)
    {
    }

    public function banks(Request $request): JsonResponse
    {
        $query = (string) $request->query('q', '');

        return response()->json([
            'items' => $this->bankLookupService->searchBanks($query),
        ]);
    }

    public function branches(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'bank_code' => ['required', 'regex:/^\d{4}$/'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json([
            'items' => $this->bankLookupService->searchBranches(
                (string) $payload['bank_code'],
                (string) ($payload['q'] ?? '')
            ),
        ]);
    }
}
