<?php

namespace Database\Seeders;

use App\Constants\PaymentConstants;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FakeReservationsSeeder extends Seeder
{
    private const BUSINESS_ID = 2;
    private const BRANCH_ID = 2;
    private const MONTHS = [7];
    private const RESERVATIONS_PER_MONTH = 20;

    /**
     * Leave empty to use all items. Example: [4, 6, 9]
     */
    private const RESERVABLE_IDS = [];

    private const PASSWORD = 'password';

    private const CUSTOMER_NAMES = [
        'Abdullah Al-Ajmi',
        'Mohammad Al-Mutairi',
        'Fahad Al-Rashidi',
        'Khaled Al-Otaibi',
        'Nasser Al-Shammari',
        'Yousef Al-Dosari',
        'Bader Al-Kandari',
        'Mubarak Al-Enezi',
        'Salem Al-Hajri',
        'Jaber Al-Khaldi',
        'Ahmad Al-Fadhli',
        'Ali Al-Harbi',
        'Hamad Al-Subaie',
        'Mishari Al-Sabah',
        'Meshari Al-Ghanim',
        'Fatma Al-Azmi',
        'Mariam Al-Bader',
        'Noor Al-Qattan',
        'Hessa Al-Mulla',
        'Dana Al-Saleh',
    ];

    private const EMPLOYEE_NAMES = [
        'Sara Al-Mansour',
        'Noura Al-Failakawi',
        'Khaled Al-Busairi',
        'Faisal Al-Roumi',
        'Mona Al-Humaidhi',
    ];

    public function run(): void
    {
        $businessId = (int) env('FAKE_RESERVATIONS_BUSINESS_ID', self::BUSINESS_ID);
        $branchId = (int) env('FAKE_RESERVATIONS_BRANCH_ID', self::BRANCH_ID);
        $months = $this->configuredMonths();
        $reservationsPerMonth = (int) env('FAKE_RESERVATIONS_PER_MONTH', self::RESERVATIONS_PER_MONTH);
        $reservableIds = $this->configuredReservableIds();

        $items = $this->reservableItems($businessId, $branchId, $reservableIds);
        if ($items->isEmpty()) {
            $this->command?->error('No reservable items found for this business/branch. Check category business_id/menu_id or FAKE_RESERVATIONS_RESERVABLE_IDS.');
            return;
        }

        DB::transaction(function () use ($businessId, $branchId, $months, $reservationsPerMonth, $items) {
            $customers = $this->seedUsers(self::CUSTOMER_NAMES, false, $businessId, $branchId);
            $employees = $this->seedUsers(self::EMPLOYEE_NAMES, true, $businessId, $branchId);
            $occupiedPeriods = [];

            foreach ($months as $month) {
                $monthStart = Carbon::create(now()->year, $month, 1)->startOfMonth();

                for ($i = 0; $i < $reservationsPerMonth; $i++) {
                    $candidate = $this->nextAvailableReservation($monthStart, $items, $occupiedPeriods);
                    if (!$candidate) {
                        continue;
                    }

                    [$item, $period] = $candidate;
                    $occupiedPeriods[$item->id][] = $period;
                    $customer = $customers->random();
                    $employee = $employees->random();
                    $reservationPrice = $this->roundedAmount(100, 1200, 50);

                    $reservation = Reservation::create([
                        'from' => $period['from'],
                        'to' => $period['to'],
                        'reservable_id' => $item->id,
                        'reservable_type' => Item::class,
                        'status' => PaymentConstants::RESERVATION_PENDING,
                        'reserved_by_id' => $employee->id,
                        'reserved_for_id' => $customer->id,
                        'follower_id' => $employee->id,
                        'business_id' => $businessId,
                        'branch_id' => $branchId,
                        'unit' => fake()->numberBetween(1, $this->itemUnits($item)),
                        'notes' => $this->reservationNotes($customer, $employee, $item, $period),
                    ]);

                    $invoices = $this->seedInvoices($reservation, $employee, $customer, $reservationPrice);
                    $reservationStatus = $this->reservationStatusFromInvoices($invoices);
                    $this->cacheReservationData($reservation, $item, $customer, $employee, $invoices, $reservationPrice, $reservationStatus);
                }
            }
        });
    }

    private function configuredReservableIds(): array
    {
        $envIds = env('FAKE_RESERVATIONS_RESERVABLE_IDS');
        if ($envIds) {
            return collect(explode(',', $envIds))
                ->map(fn(string $id) => (int) trim($id))
                ->filter()
                ->values()
                ->all();
        }

        return self::RESERVABLE_IDS;
    }

    private function nextAvailableReservation(Carbon $monthStart, Collection $items, array $occupiedPeriods): ?array
    {
        $attempts = max(20, $items->count() * 10);

        for ($i = 0; $i < $attempts; $i++) {
            $item = $items->random();
            $period = $this->reservationPeriod($monthStart);

            if ($this->periodIsAvailable($period, $occupiedPeriods[$item->id] ?? [])) {
                return [$item, $period];
            }
        }

        return null;
    }

    private function configuredMonths(): array
    {
        $envMonths = env('FAKE_RESERVATIONS_MONTHS');
        if (!$envMonths) {
            return self::MONTHS;
        }

        $decoded = json_decode($envMonths, true);
        $months = is_array($decoded) ? $decoded : explode(',', $envMonths);

        $months = collect($months)
            ->map(fn($month) => (int) trim((string) $month))
            ->filter(fn(int $month) => $month >= 1 && $month <= 12)
            ->unique()
            ->values()
            ->all();

        return $months ?: self::MONTHS;
    }

    private function reservableItems(int $businessId, int $branchId, array $reservableIds): Collection
    {
        $branch = Branch::find($branchId);
        if (!$branch || (int) $branch->business_id !== $businessId || !$branch->menu_id) {
            return collect();
        }

        return Item::with(['locales', 'itemable'])
            ->whereHas('category', function ($query) use ($businessId, $branch) {
                $query->where('business_id', $businessId)
                    ->where('menu_id', $branch->menu_id);
            })
            ->when($reservableIds, fn($query) => $query->whereIn('id', $reservableIds))
            ->get();
    }

    private function seedUsers(array $names, bool $isEmployee, int $businessId, int $branchId): Collection
    {
        $usedPhones = [];

        return collect($names)->map(function (string $name, int $index) use ($isEmployee, $businessId, $branchId, &$usedPhones) {
            $emailPrefix = $this->emailPrefixFromName($name, $isEmployee, $index);
            $phone = $this->uniquePhone($usedPhones);
            $usedPhones[] = $phone;

            return User::updateOrCreate(
                ['email' => sprintf('%s@barq.test', $emailPrefix)],
                [
                    'name' => $name,
                    'phone' => $phone,
                    'password' => Hash::make(self::PASSWORD),
                    'email_verified_at' => now(),
                    'business_id' => $isEmployee ? $businessId : null,
                    'dashboard_access' => $isEmployee,
                    'is_employee' => $isEmployee,
                    'control' => $isEmployee ? [[
                        'business_id' => $businessId,
                        'branch_ids' => [$branchId],
                    ]] : null,
                ]
            );
        });
    }

    private function uniquePhone(array $usedPhones): string
    {
        do {
            $phone = '5' . random_int(1000000, 9999999);
        } while (in_array($phone, $usedPhones, true) || User::where('phone', $phone)->exists());

        return $phone;
    }

    private function reservationPeriod(Carbon $monthStart): array
    {
        $pattern = fake()->randomElement(['weekend', 'weekday', 'full_sun_sat', 'full_thu_wed']);

        return match ($pattern) {
            'weekend' => $this->periodStartingOn($monthStart, Carbon::THURSDAY, 2),
            'weekday' => $this->periodStartingOn($monthStart, Carbon::SUNDAY, 3),
            'full_sun_sat' => $this->periodStartingOn($monthStart, Carbon::SUNDAY, 6),
            'full_thu_wed' => $this->periodStartingOn($monthStart, Carbon::THURSDAY, 6),
        };
    }

    private function periodStartingOn(Carbon $monthStart, int $startDayOfWeek, int $days): array
    {
        $starts = [];
        $cursor = $monthStart->copy()->startOfMonth();
        $month = $cursor->month;

        while ($cursor->month === $month) {
            if ($cursor->dayOfWeek === $startDayOfWeek) {
                $starts[] = $cursor->copy();
            }

            $cursor->addDay();
        }

        $fromDate = collect($starts)
            ->filter(fn(Carbon $date) => $date->copy()->addDays($days)->month === $month)
            ->random();

        return [
            'from' => $fromDate->copy()->setTime(14, 0),
            'to' => $fromDate->copy()->addDays($days)->setTime(12, 0),
        ];
    }

    private function emailPrefixFromName(string $name, bool $isEmployee, int $index): string
    {
        $parts = collect(explode('_', Str::slug($name, '_')))
            ->filter()
            ->values();

        $initial = substr($parts->first() ?? ($isEmployee ? 'employee' : 'user'), 0, 1);
        $last = $parts->slice(-1)->first() ?? ($isEmployee ? 'employee' : 'user');

        return strtolower("{$initial}_{$last}_" . (2020 + $index));
    }

    private function reservationNotes(User $customer, User $employee, Item $item, array $period): array
    {
        $notes = [
            sprintf('Customer %s booked %s.', $customer->name, $this->itemLabel($item)),
            sprintf('Handled by %s.', $employee->name),
        ];

        if (fake()->boolean(50)) {
            $notes[] = sprintf(
                'Reservation window: %s to %s.',
                $period['from']->format('Y-m-d H:i'),
                $period['to']->format('Y-m-d H:i')
            );
        }

        return $notes;
    }

    private function periodIsAvailable(array $period, array $occupiedPeriods): bool
    {
        foreach ($occupiedPeriods as $occupiedPeriod) {
            if ($this->periodsOverlap($period, $occupiedPeriod)) {
                return false;
            }
        }

        return true;
    }

    private function periodsOverlap(array $first, array $second): bool
    {
        return $first['from']->lt($second['to']) && $first['to']->gt($second['from']);
    }

    private function seedInvoices(Reservation $reservation, User $employee, User $customer, int $reservationPrice): Collection
    {
        $invoiceCount = fake()->numberBetween(0, 4);

        if ($invoiceCount === 0) {
            return collect();
        }

        $hasInsurance = $invoiceCount > 1 && fake()->boolean(65);
        $insuranceAmount = $hasInsurance
            ? min($this->roundedAmount(50, 200, 50), $reservationPrice - 50)
            : 0;
        $creditTotal = $reservationPrice + $insuranceAmount;
        $creditCount = $invoiceCount - ($hasInsurance ? 1 : 0);
        $creditAmounts = $this->splitRoundedAmount($creditTotal, max(1, $creditCount));
        $paidCreditCount = fake()->numberBetween(0, count($creditAmounts));

        $rows = collect($creditAmounts)->map(function (int $amount, int $index) use ($reservation, $employee, $customer, $paidCreditCount) {
            return [
                'amount' => $amount,
                'note' => $index === 0 ? 'Reservation payment' : 'Reservation payment installment',
                'type' => PaymentConstants::INVOICE_CREDIT,
                'status' => $index < $paidCreditCount
                    ? PaymentConstants::INVOICE_PAID
                    : PaymentConstants::INVOICE_PENDING,
                'payment_type' => fake()->randomElement([
                    PaymentConstants::TYPE_CASH,
                    PaymentConstants::TYPE_KNET,
                    PaymentConstants::TYPE_ONLINE,
                    PaymentConstants::TYPE_LINK,
                    PaymentConstants::TYPE_TRANSFER,
                ]),
                'reservation_id' => $reservation->id,
                'invoice_by_id' => $employee->id,
                'invoice_for_id' => $customer->id,
                'business_id' => $reservation->business_id,
                'branch_id' => $reservation->branch_id,
                'reference_id' => strtoupper(uniqid('FR')),
                'status_changed_at' => now(),
            ];
        });

        if ($hasInsurance) {
            $rows->push([
                'amount' => $insuranceAmount,
                'note' => 'Insurance',
                'description' => 'Insurance amount',
                'type' => PaymentConstants::INVOICE_DEBIT,
                'status' => $paidCreditCount > 0
                    ? fake()->randomElement([
                        PaymentConstants::INVOICE_PENDING,
                        PaymentConstants::INVOICE_PAID,
                    ])
                    : PaymentConstants::INVOICE_PENDING,
                'payment_type' => fake()->randomElement([
                    PaymentConstants::TYPE_CASH,
                    PaymentConstants::TYPE_KNET,
                    PaymentConstants::TYPE_TRANSFER,
                ]),
                'reservation_id' => $reservation->id,
                'invoice_by_id' => $employee->id,
                'invoice_for_id' => $customer->id,
                'business_id' => $reservation->business_id,
                'branch_id' => $reservation->branch_id,
                'reference_id' => strtoupper(uniqid('FR')),
                'status_changed_at' => now(),
            ]);
        }

        if (Schema::hasColumn('invoices', 'description')) {
            $rows = $rows->map(function (array $row) {
                $row['description'] = $row['type'] === PaymentConstants::INVOICE_DEBIT
                    ? 'Insurance amount'
                    : fake()->randomElement([
                        'Reservation payment',
                        'Advance payment',
                        'Partial payment for reservation',
                    ]);

                return $row;
            });
        }

        if (Schema::hasColumn('invoices', 'paid_at')) {
            $rows = $rows->map(function (array $row) {
                $row['paid_at'] = $row['status'] === PaymentConstants::INVOICE_PAID ? now() : null;
                return $row;
            });
        }

        return $rows
            ->shuffle()
            ->map(fn(array $row) => Invoice::create($row))
            ->values();
    }

    private function splitRoundedAmount(int $amount, int $parts): array
    {
        if ($parts === 1) {
            return [$amount];
        }

        $remaining = $amount;
        $amounts = [];

        for ($i = $parts; $i > 1; $i--) {
            $max = $remaining - (($i - 1) * 50);
            $piece = $this->roundedAmount(50, max(50, $max), 50);
            $amounts[] = $piece;
            $remaining -= $piece;
        }

        $amounts[] = $remaining;

        return $amounts;
    }

    private function roundedAmount(int $min, int $max, int $step): int
    {
        return fake()->numberBetween((int) ceil($min / $step), (int) floor($max / $step)) * $step;
    }

    private function itemUnits(Item $item): int
    {
        return max(1, (int) ($item->itemable?->units ?? 1));
    }

    private function itemLabel(Item $item): string
    {
        $label = $item->locales->firstWhere('locale', 'en')?->name
            ?? $item->locales->first()?->name
            ?? "Item {$item->id}";

        return $label;
    }

    private function cacheReservationData(
        Reservation $reservation,
        Item $item,
        User $customer,
        User $employee,
        Collection $invoices,
        int $reservationPrice,
        string $reservationStatus
    ): void {
        $reservation->update([
            'status' => $reservationStatus,
            'data' => [
                'reservable' => $item->toArray(),
                'reserved_for' => $this->cleanUserData($customer),
                'reserved_by' => $this->cleanUserData($employee),
                'follower' => $this->cleanUserData($employee),
                'invoices' => $invoices->map->toArray()->all(),
                'subtotal_price' => $reservationPrice,
                'total_price' => $reservationPrice,
            ],
        ]);
    }

    private function cleanUserData(User $user): array
    {
        $data = $user->toArray();
        $data['business_control'] = null;

        return $data;
    }

    private function reservationStatusFromInvoices(Collection $invoices): string
    {
        return $invoices->contains(fn(Invoice $invoice) => $invoice->status === PaymentConstants::INVOICE_PAID)
            ? PaymentConstants::RESERVATION_COMPLETED
            : PaymentConstants::RESERVATION_PENDING;
    }
}
