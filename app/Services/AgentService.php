<?php

namespace App\Services;

use App\Constants\AuditServices;
use App\Constants\PaymentConstants;
use App\Events\NewReservation;
use App\Jobs\SendNewReservationNotification;
use App\Models\Business;
use App\Models\Item;
use App\Models\Reservation;
use App\Repository\Eloquent\ReservationRepository;
use App\Services\AgentTools\ReservationAIService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AgentService
{
    private string $apiKey;
    private string $model = 'gpt-4o-mini';
    private string $baseUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
    }

    /**
     * Process a user message and return the agent's reply.
     *
     * @param array  $history  Previous conversation messages [['role'=>'user'|'assistant', 'content'=>'...']]
     * @param string $userMessage
     * @param int    $userId   Authenticated user ID
     * @param int    $businessId
     * @param int    $branchId
     * @return array ['reply' => string, 'history' => array]
     */
    public function chat(array $history, string $userMessage, int $userId, int $businessId, int $branchId): array
    {
        $messages = $this->buildMessages($history, $userMessage);

        $response = $this->callOpenAI($messages, $this->getTools());

        $result = $response->json();

        if (!isset($result['choices'][0])) {
            Log::error('OpenAI unexpected response', [
                'http_status'    => $response->status(),
                'openai_error'   => $result['error'] ?? null,
                'full_response'  => $result,
            ]);

            $openAiMessage = $result['error']['message'] ?? null;

            return [
                'reply'   => $openAiMessage
                    ? "OpenAI error: {$openAiMessage}"
                    : 'حدث خطأ، يرجى المحاولة مرة أخرى. / An error occurred, please try again.',
                'history' => $history,
            ];
        }

        $message = $result['choices'][0]['message'];

        // Append assistant message to history
        $messages[] = $message;

        // Handle tool calls in a loop (the model may chain multiple tool calls)
        while (isset($message['tool_calls']) && count($message['tool_calls']) > 0) {
            foreach ($message['tool_calls'] as $toolCall) {
                $toolName = $toolCall['function']['name'];
                $toolArgs = json_decode($toolCall['function']['arguments'], true) ?? [];

                Log::debug('Agent tool call', ['tool' => $toolName, 'args' => $toolArgs]);

                $toolResult = $this->executeTool($toolName, $toolArgs, $userId, $businessId, $branchId);

                $messages[] = [
                    'role'         => 'tool',
                    'tool_call_id' => $toolCall['id'],
                    'content'      => json_encode($toolResult, JSON_UNESCAPED_UNICODE),
                ];
            }

            // Call the model again with tool results
            $response = $this->callOpenAI($messages, $this->getTools());
            $result   = $response->json();

            if (!isset($result['choices'][0])) {
                break;
            }

            $message    = $result['choices'][0]['message'];
            $messages[] = $message;
        }

        $reply = $message['content'] ?? '';

        // Build history preserving tool calls and tool results so the model
        // retains context (e.g. resolved item_id) across subsequent requests.
        $cleanHistory = [];
        foreach ($messages as $m) {
            if ($m['role'] === 'system') {
                continue; // system prompt is rebuilt fresh each request
            }
            $cleanHistory[] = $m;
        }

        return ['reply' => $reply, 'history' => $cleanHistory];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function buildMessages(array $history, string $userMessage): array
    {
        $today = Carbon::now()->toDateString();

        $systemPrompt = <<<PROMPT
You are a friendly and helpful booking assistant for a chalet and unit reservation platform.

Today's date is {$today}.

LANGUAGE RULES — this is critical:
- Detect the language the user is writing in and always reply in the exact same language and dialect.
- If the user speaks English → reply in English.
- If the user speaks Modern Standard Arabic (فصحى) → reply in فصحى.
- If the user speaks Kuwaiti Arabic dialect → reply in Kuwaiti dialect (اللهجة الكويتية).
- If the user mixes languages → detect which language dominates and reply in that one.
- Never switch languages unless the user switches first.

BOOKING RULES:
- When a user wants to make a reservation, follow this exact flow and do NOT repeat any step:
  STEP 1 — Identify the chalet: If the user mentions a name, call search_units_by_name immediately to get the item_id. Once you have the item_id, store it mentally and NEVER ask for the chalet name again.
  STEP 2 — Collect dates: Ask for check-in and check-out date and time if not already provided.
  STEP 3 — Check availability: Once you have item_id AND dates, call check_availability for that specific item_id. Do NOT call get_available_units at this stage unless the specific unit is unavailable.
  STEP 4 — Confirm with user: Show a summary (chalet name, dates) and ask the user to confirm before creating.
  STEP 5 — Create reservation: Only after user confirms (نعم / yes / ok / اوكي / تمام / proceed), call create_reservation with the item_id and dates you already have.
- CRITICAL: Once item_id is resolved in STEP 1, carry it forward through ALL subsequent steps. Never ask for the chalet name or unit again.
- CRITICAL: Once dates are provided in STEP 2, carry them forward. Never ask for dates again.
- If the requested unit is not available for the chosen dates, then (and only then) call get_available_units and present alternatives politely.
- If the user provides relative dates like "next Thursday" or "الخميس الجاي" or "بعد بكرة", resolve them to actual calendar dates based on today's date ({$today}) before proceeding.
- After successfully creating a reservation, confirm the details to the user clearly.
- Be concise, warm, and conversational.

CONTEXT AWARENESS:
- Always review the full conversation history before responding. Extract any item_id, check_in, check_out already established in prior turns.
- If you already called search_units_by_name and got an item_id, use it — do NOT call it again or ask for the name again.
- If you already have dates from a previous message, use them — do NOT ask for dates again.
- If the user says "yes", "نعم", "ok", "اوكي", "تمام", or any affirmation, treat it as confirmation of the last thing you asked them to confirm, and proceed accordingly.
- Never ask "how can I help you?" if the user is clearly mid-flow in a booking conversation.
- Never restart the booking flow from scratch mid-conversation.
PROMPT;

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        // Append prior conversation history, preserving tool_calls and tool results
        foreach ($history as $turn) {
            if (!isset($turn['role'])) {
                continue;
            }

            // Tool result messages have role=tool and a tool_call_id
            if ($turn['role'] === 'tool' && isset($turn['tool_call_id'], $turn['content'])) {
                $messages[] = $turn;
                continue;
            }

            // Assistant messages may have null content when they contain tool_calls
            if ($turn['role'] === 'assistant') {
                if (isset($turn['tool_calls']) || isset($turn['content'])) {
                    $messages[] = $turn;
                }
                continue;
            }

            // Regular user/system messages
            if (isset($turn['content'])) {
                $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
            }
        }

        // Append the new user message
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }

    private function callOpenAI(array $messages, array $tools): \Illuminate\Http\Client\Response
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post($this->baseUrl, [
                'model'       => $this->model,
                'messages'    => $messages,
                'tools'       => $tools,
                'tool_choice' => 'auto',
            ]);

        if ($response->failed()) {
            Log::error('OpenAI API HTTP error', [
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);
        }

        return $response;
    }

    // -------------------------------------------------------------------------
    // Tool definitions (sent to GPT-4o)
    // -------------------------------------------------------------------------

    private function getTools(): array
    {
        return [
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'search_units_by_name',
                    'description' => 'Search for chalets or units by name. Always call this first when the user mentions a chalet or unit by name to get the correct item_id before checking availability or creating a reservation. Never guess an item_id.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'The chalet or unit name to search for (partial match is supported)'],
                        ],
                        'required'   => ['name'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_available_units',
                    'description' => 'Get all available chalets/units for a given date range. Use this to show options or find alternatives when the requested unit is booked.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'check_in'  => ['type' => 'string', 'description' => 'Check-in date and time in Y-m-d H:i:s or Y-m-d format'],
                            'check_out' => ['type' => 'string', 'description' => 'Check-out date and time in Y-m-d H:i:s or Y-m-d format'],
                        ],
                        'required'   => ['check_in', 'check_out'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'check_availability',
                    'description' => 'Check if a specific chalet/unit (by item_id) is available for given dates.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'item_id'   => ['type' => 'integer', 'description' => 'The ID of the chalet or unit'],
                            'check_in'  => ['type' => 'string', 'description' => 'Check-in date and time'],
                            'check_out' => ['type' => 'string', 'description' => 'Check-out date and time'],
                            'unit'      => ['type' => 'integer', 'description' => 'Unit number (default 1)'],
                        ],
                        'required'   => ['item_id', 'check_in', 'check_out'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_unit_details',
                    'description' => 'Get details about a specific chalet or unit by its item ID, including name and description.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'item_id' => ['type' => 'integer', 'description' => 'The ID of the chalet or unit'],
                        ],
                        'required'   => ['item_id'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'create_reservation',
                    'description' => 'Create a reservation for the authenticated user after confirming all details.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => [
                            'item_id'   => ['type' => 'integer', 'description' => 'The ID of the chalet or unit to reserve'],
                            'check_in'  => ['type' => 'string', 'description' => 'Check-in date and time in Y-m-d H:i:s format'],
                            'check_out' => ['type' => 'string', 'description' => 'Check-out date and time in Y-m-d H:i:s format'],
                            'unit'      => ['type' => 'integer', 'description' => 'Unit number (default 1)'],
                            'notes'     => ['type' => 'string', 'description' => 'Optional notes from the user'],
                        ],
                        'required'   => ['item_id', 'check_in', 'check_out'],
                    ],
                ],
            ],
            [
                'type'     => 'function',
                'function' => [
                    'name'        => 'get_user_reservations',
                    'description' => 'Get the current authenticated user\'s upcoming or recent reservations.',
                    'parameters'  => [
                        'type'       => 'object',
                        'properties' => (object)[],
                    ],
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Tool execution — wired to existing Laravel services & models
    // -------------------------------------------------------------------------

    private function executeTool(string $name, array $args, int $userId, int $businessId, int $branchId): array
    {
        try {
            $reservationAIService = new ReservationAIService();
            return match ($name) {
                'search_units_by_name' => $reservationAIService->toolGetSearchUnitsByName($args, $businessId, $branchId),
                'get_available_units'  => $reservationAIService->toolGetAvailableUnits($args, $businessId, $branchId),
                'check_availability'   => $reservationAIService->toolCheckAvailability($args, $businessId, $branchId),
                'get_unit_details'     => $reservationAIService->toolGetUnitDetails($args),
                'create_reservation'   => $reservationAIService->toolCreateReservation($args, $userId, $businessId, $branchId),
                'get_user_reservations'=> $reservationAIService->toolGetUserReservations($userId, $businessId, $branchId),
                default                => ['error' => "Unknown tool: {$name}"],
            };
        } catch (\Throwable $e) {
            Log::error('Agent tool execution failed', ['tool' => $name, 'error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

}
