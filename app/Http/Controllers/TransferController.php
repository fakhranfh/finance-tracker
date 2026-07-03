<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Transfer;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;

class TransferController extends Controller
{
    public function __construct(
        private TransferService $transferService,
    ) {}

    public function destroy(Transfer $transfer): JsonResponse
    {
        $this->authorize('delete', $transfer);

        try {
            $this->transferService->cancelTransfer($transfer->id);
        } catch (InsufficientBalanceException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Transfer cancelled and wallet balances restored.']);
    }
}
