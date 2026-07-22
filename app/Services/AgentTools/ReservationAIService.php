<?php

namespace App\Services\AgentTools;

use App\Constants\AuditServices;
use App\Constants\PaymentConstants;
use App\Events\NewReservation;
use App\Jobs\SendNewReservationNotification;
use App\Models\Business;
use App\Models\Item;
use App\Models\Reservation;
use App\Repository\Eloquent\ReservationRepository;
use App\Services\AuditService;
use Carbon\Carbon;

class ReservationAIService
{

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

    public function toolGetSearchUnitsByName(array $args, int $businessId, int $branchId): array
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
                    'id'      => $item->id,
                    'locales' => $item->locales->map(fn($l) => ['locale' => $l->locale, 'name' => $l->name])->values()->toArray(),
                ];
            })->values()->toArray();
        }

        // Second attempt: normalized LIKE search (always run, catches variants like "zawya" → "Zawaya")
        $items = Item::with(['itemable', 'locales'])
            ->where(function ($q) use ($businessId) {
                $q->whereHas('category', function ($q2) use ($businessId) {
                    $q2->where('business_id', $businessId);
                });
            })
            ->whereHas('locales', function ($q) use ($normalizedSearch) {
                $q->where('name', 'LIKE', "%{$normalizedSearch}%");
            })
            ->limit(5)
            ->get();

        \Log::info('Normalized search results for "'.$normalizedSearch.'":', $items->toArray());

        if ($items->isNotEmpty()) {
            return $items->map(function ($item) {
                return [
                    'id'      => $item->id,
                    'locales' => $item->locales->map(fn($l) => ['locale' => $l->locale, 'name' => $l->name])->values()->toArray(),
                ];
            })->values()->toArray();
        }

        // Fallback: in-memory similarity + levenshtein scoring
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

                    // Levenshtein bonus: reward close matches on short strings
                    // e.g. "zawya" vs "zawaya" = distance of 1
                    $lev = levenshtein($normalizedSearch, $name);
                    $maxLen = max(mb_strlen($normalizedSearch), mb_strlen($name));
                    if ($maxLen > 0) {
                        $levScore = (1 - $lev / $maxLen) * 100;
                        $score = max($score, $levScore);
                    }

                    // Bonus for substring containment in either direction
                    if (str_contains($name, $normalizedSearch) || str_contains($normalizedSearch, $name)) {
                        $score += 20;
                    }

                    // Bonus for matching individual words
                    foreach (explode(' ', $normalizedSearch) as $word) {
                        if ($word && mb_strlen($word) > 2 && str_contains($name, $word)) {
                            $score += 10;
                        }
                    }

                    $bestScore = max($bestScore, $score);
                }

                $item->search_score = $bestScore;

                return $item;
            })
            ->filter(fn ($item) => $item->search_score > 50) // levenshtein-based scores are 0-100
            ->sortByDesc('search_score')
            ->take(5)
            ->values();

        \Log::info('Similarity search results:', $results->toArray());

        return $results->map(function ($item) {
            return [
                'id'      => $item->id,
                'locales' => $item->locales->map(fn($l) => ['locale' => $l->locale, 'name' => $l->name])->values()->toArray(),
            ];
        })->values()->toArray();
    }

    public function toolGetAvailableUnits(array $args, int $businessId, int $branchId): array
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

    public function toolCheckAvailability(array $args, int $businessId, int $branchId): array
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

    public function toolGetUnitDetails(array $args): array
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

    public function toolCreateReservation(array $args, int $userId, int $businessId, int $branchId): array
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

        AuditService::log(AuditServices::Reservations, $reservation->id, "Created booking " . $reservation->id, $businessId, $branchId);

        event(new NewReservation($reservation->id));
        dispatch(new SendNewReservationNotification($reservation->id));

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

    public function toolGetUserReservations(int $userId, int $businessId, int $branchId): array
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
