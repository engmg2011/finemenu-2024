<?php

namespace App\Services;

use App\Constants\PaymentConstants;
use App\Models\Business;
use App\Models\Item;
use App\Models\Reservation;
use App\Repository\Eloquent\ReservationRepository;
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
            return match ($name) {
                'search_units_by_name' => $this->toolGetSearchUnitsByName($args, $businessId, $branchId),
                'get_available_units'  => $this->toolGetAvailableUnits($args, $businessId, $branchId),
                'check_availability'   => $this->toolCheckAvailability($args, $businessId, $branchId),
                'get_unit_details'     => $this->toolGetUnitDetails($args),
                'create_reservation'   => $this->toolCreateReservation($args, $userId, $businessId, $branchId),
                'get_user_reservations'=> $this->toolGetUserReservations($userId, $businessId, $branchId),
                default                => ['error' => "Unknown tool: {$name}"],
            };
        } catch (\Throwable $e) {
            Log::error('Agent tool execution failed', ['tool' => $name, 'error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Normalize Arabic + English text.
     */
    private function normalizeText(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');

        // Remove Arabic diacritics
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);

        // Normalize Arabic letters
        $replace = [
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ى' => 'ي',
            'ؤ' => 'و',
            'ئ' => 'ي',
            'ة' => 'ه',
        ];

        $text = strtr($text, $replace);

        // Remove punctuation
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);

        // Remove duplicate spaces
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    private function toolGetSearchUnitsByName(array $args, int $businessId, int $branchId): array
    {
        $search = trim($args['name']);

        if ($search === '') {
            return [];
        }

        $normalizedSearch = $this->normalizeText($search);

        // First try direct match
        $items = Item::with(['itemable', 'locales'])
            ->where(function ($q) use ($businessId) {
                $q->whereHas('category', function ($q2) use ($businessId) {
                    $q2->where('business_id', $businessId);
                });
            })
            ->whereHas('locales', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            })
            ->limit(5)
            ->get();

        \Log::info('Search results for "'.$search.'":', $items->toArray());

        if ($items->isNotEmpty()) {
            return $items->map(function ($item) {
                return [
                    'id'       => $item->id,
                    'locales'  => $item->locales->map(fn($l) => ['locale' => $l->locale, 'name' => $l->name])->values(),
                ];
            })->values()->toArray();
        }

        // Fallback: similarity search
        $items = Item::with('locales')
            ->where(function ($q) use ($businessId) {
                $q->whereHas('category', function ($q2) use ($businessId) {
                    $q2->where('business_id', $businessId);
                });
            })->get();

        $results = $items
            ->map(function ($item) use ($normalizedSearch) {

                $bestScore = 0;

                foreach ($item->locales as $locale) {

                    $name = $this->normalizeText($locale->name);

                    similar_text($normalizedSearch, $name, $score);

                    // Bonus for matching individual words
                    foreach (explode(' ', $normalizedSearch) as $word) {
                        if ($word && str_contains($name, $word)) {
                            $score += 10;
                        }
                    }

                    $bestScore = max($bestScore, $score);
                }

                $item->search_score = $bestScore;

                return $item;
            })
            ->filter(fn ($item) => $item->search_score > 20) // minimum similarity
            ->sortByDesc('search_score')
            ->take(5)
            ->values();

        \Log::info('Similarity search results:', $results->toArray());

        return $results->map(function ($item) {
            return [
                'id'      => $item->id,
                'locales' => $item->locales->map(fn($l) => ['locale' => $l->locale, 'name' => $l->name])->values(),
            ];
        })->values()->toArray();
    }

    private function toolGetAvailableUnits(array $args, int $businessId, int $branchId): array
    {
        $from = Carbon::parse($args['check_in'])->toDateTimeString();
        $to   = Carbon::parse($args['check_out'])->toDateTimeString();

        $available = Item::whereHas('itemable')
            ->with(['itemable', 'locales'])
            ->where(function ($q) use ($businessId) {
                $q->whereHas('category', function ($q2) use ($businessId) {
                    $q2->where('business_id', $businessId);
                });
            })
            ->withCount(['reservations as reserved_units' => function ($q) use ($from, $to) {
                $q->where('status', '!=', PaymentConstants::RESERVATION_CANCELED)
                    ->where('from', '<', $to)
                    ->where('to', '>', $from);
            }])
            ->get()
            ->filter(function ($item) {
                $units = $item->itemable->units ?? 1;
                return $item->reserved_units < $units;
            })
            ->values();

        if ($available->isEmpty()) {
            return ['available' => false, 'message' => 'No units available for the selected dates.'];
        }

        $list = $available->map(function ($item) {
            $name = optional($item->locales->first())->name ?? "Unit #{$item->id}";
            return [
                'item_id'  => $item->id,
                'name'     => $name,
                'units'    => $item->itemable->units ?? 1,
            ];
        })->values()->toArray();

        return ['available' => true, 'units' => $list];
    }

    private function toolCheckAvailability(array $args, int $businessId, int $branchId): array
    {
        $itemId   = (int) $args['item_id'];
        $from     = Carbon::parse($args['check_in'])->toDateTimeString();
        $to       = Carbon::parse($args['check_out'])->toDateTimeString();
        $unitNum  = (int) ($args['unit'] ?? 1);

        $item = Item::with('itemable')->find($itemId);
        if (!$item) {
            return ['available' => false, 'message' => 'Unit not found.'];
        }

        $conflicting = Reservation::where('reservable_id', $itemId)
            ->where('status', '!=', PaymentConstants::RESERVATION_CANCELED)
            ->where('unit', $unitNum)
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('from', [$from, $to])
                  ->orWhereBetween('to', [$from, $to])
                  ->orWhere(function ($q2) use ($from, $to) {
                      $q2->where('from', '<=', $from)->where('to', '>=', $to);
                  });
            })
            ->exists();

        $name = optional($item->locales->first())->name ?? "Unit #{$item->id}";

        return [
            'available' => !$conflicting,
            'item_id'   => $itemId,
            'name'      => $name,
            'unit'      => $unitNum,
            'check_in'  => $from,
            'check_out' => $to,
        ];
    }

    private function toolGetUnitDetails(array $args): array
    {
        $item = Item::with(['itemable', 'locales'])->find((int) $args['item_id']);
        if (!$item) {
            return ['error' => 'Unit not found.'];
        }

        $locale = $item->locales->first();
        return [
            'item_id'     => $item->id,
            'name'        => $locale->name ?? "Unit #{$item->id}",
            'description' => $locale->description ?? null,
            'units'       => $item->itemable->units ?? 1,
        ];
    }

    private function toolCreateReservation(array $args, int $userId, int $businessId, int $branchId): array
    {
        $itemId   = (int) $args['item_id'];
        $from     = Carbon::parse($args['check_in'])->toDateTimeString();
        $to       = Carbon::parse($args['check_out'])->toDateTimeString();
        $unit     = (int) ($args['unit'] ?? 1);
        $notes    = $args['notes'] ?? null;

        $item = Item::with('itemable')->find($itemId);
        if (!$item) {
            return ['success' => false, 'message' => 'Unit not found.'];
        }

        // Check availability first
        $availability = $this->toolCheckAvailability([
            'item_id'   => $itemId,
            'check_in'  => $from,
            'check_out' => $to,
            'unit'      => $unit,
        ], $businessId, $branchId);

        if (!($availability['available'] ?? false)) {
            return ['success' => false, 'message' => 'The unit is not available for the selected dates.'];
        }

        $business = Business::find($businessId);

        $reservationData = [
            'reservable_id'   => $itemId,
            'reservable_type' => Item::class,
            'from'            => $from,
            'to'              => $to,
            'unit'            => $unit,
            'status'          => PaymentConstants::RESERVATION_PENDING,
            'reserved_by_id'  => $userId,
            'reserved_for_id' => $userId,
            'business_id'     => $businessId,
            'branch_id'       => $branchId,
            'notes'           => $notes ? ['note' => $notes] : null,
        ];

        $reservation = Reservation::create($reservationData);

        app(ReservationRepository::class)->setReservationCashedData($reservation->id);

        $name = optional($item->locales->first())->name ?? "Unit #{$item->id}";

        return [
            'success'        => true,
            'reservation_id' => $reservation->id,
            'unit_name'      => $name,
            'check_in'       => $from,
            'check_out'      => $to,
            'unit'           => $unit,
            'status'         => $reservation->status,
        ];
    }

    private function toolGetUserReservations(int $userId, int $businessId, int $branchId): array
    {
        $reservations = Reservation::with(['reservable.locales'])
            ->where('reserved_for_id', $userId)
            ->where('business_id', $businessId)
            ->where('branch_id', $branchId)
            ->where('status', '!=', PaymentConstants::RESERVATION_CANCELED)
            ->orderByDesc('from')
            ->limit(10)
            ->get()
            ->map(function ($r) {
                $name = optional(optional($r->reservable)->locales->first())->name ?? 'Unknown';
                return [
                    'reservation_id' => $r->id,
                    'unit_name'      => $name,
                    'check_in'       => $r->from,
                    'check_out'      => $r->to,
                    'status'         => $r->status,
                ];
            })
            ->values()
            ->toArray();

        return ['reservations' => $reservations];
    }
}
