<?php

namespace App\Http\Controllers;

use App\Services\AgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AgentController extends Controller
{
    public function __construct(private AgentService $agentService)
    {
    }

    /**
     * POST /api/agent/chat
     *
     * Request body:
     * {
     *   "message": "أبي أحجز شاليه",
     *   "history": [],          // optional, array of prior turns
     *   "business_id": 1,
     *   "branch_id": 1
     * }
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message'     => 'required|string|max:2000',
            'history'     => 'nullable|array',
            'history.*.role'    => 'required_with:history|in:user,assistant,tool',
            'history.*.content' => 'nullable|string',
            'business_id' => 'required|integer|exists:business,id',
            'branch_id'   => 'required|integer|exists:branches,id',
        ]);

        $user       = auth('sanctum')->user();
        $history    = $request->input('history', []);
        $message    = $request->input('message');
        $businessId = (int) $request->input('business_id');
        $branchId   = (int) $request->input('branch_id');

        Log::info('Agent chat request', [
            'user_id'     => $user->id,
            'business_id' => $businessId,
            'branch_id'   => $branchId,
            'message'     => $message,
        ]);

        $result = $this->agentService->chat(
            history:    $history,
            userMessage: $message,
            userId:     $user->id,
            businessId: $businessId,
            branchId:   $branchId,
        );

        return response()->json([
            'reply'   => $result['reply'],
            'history' => $result['history'],
        ]);
    }
}
