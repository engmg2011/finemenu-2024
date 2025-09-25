<?php

namespace App\Http\Controllers;

use App\Constants\PaymentConstants;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;

class StatisticsController extends Controller
{

    public function getCustomersCount()
    {
        $businessId = request()->route('businessId');
        return User::where('business_id', $businessId)
            ->where('is_employee', false)->count();
    }

    public function getEmployeesCount()
    {
        $businessId = request()->route('businessId');
        return User::where('business_id', $businessId)
            ->where('is_employee', true)->count();
    }

    public function getCategoriesCount()
    {
        $businessId = request()->route('businessId');
        $menuIds = Menu::where('business_id', $businessId)->pluck('id');
        return Category::whereIn('menu_id', $menuIds)->count();
    }

    public function getItemsCount()
    {
        $businessId = request()->route('businessId');
        $menuIds = Menu::where('business_id', $businessId)->pluck('id');
        $categoryIds = Category::whereIn('menu_id', $menuIds)->pluck('id');
        return Item::whereIn('category_id', $categoryIds)->count();
    }

    public function getBasicStatistics()
    {
        return response()->json([
            'customers' => $this->getCustomersCount(),
            'employees' => $this->getEmployeesCount(),
            'categories' => $this->getCategoriesCount(),
            'items' => $this->getItemsCount()
        ]);
    }


    /**
     * Reservations [ALL, Upcoming, Current, Past, Canceled]
     * Reservations Progress [3 weeks before, 2 weeks after ]
     */
    public function getALLReservationsCount()
    {
        $businessId = request()->route('businessId');
        return Reservation::where('business_id', $businessId)->count();
    }

    public function getUpcomingReservationsCount()
    {
        $businessId = request()->route('businessId');
        return Reservation::where('business_id', $businessId)
            ->where('from', '>=', now()->toDateString())->count();
    }

    public function getCurrentReservationsCount()
    {
        $businessId = request()->route('businessId');
        return Reservation::where('business_id', $businessId)
            ->where('from', '<=', now()->toDateString())
            ->where('to', '>=', now()->toDateString())->count();
    }

    public function getPastReservationsCount()
    {
        $businessId = request()->route('businessId');
        return Reservation::where('business_id', $businessId)
            ->where('to', '<', now()->toDateString())->count();
    }

    public function getCanceledReservationsCount()
    {
        $businessId = request()->route('businessId');
        return Reservation::where('business_id', $businessId)
            ->where('status', PaymentConstants::RESERVATION_CANCELED)->count();
    }

    public function getReservationsStatistics()
    {
        return response()->json([
            'all' => $this->getALLReservationsCount(),
            'upcoming' => $this->getUpcomingReservationsCount(),
            'current' => $this->getCurrentReservationsCount(),
            'past' => $this->getPastReservationsCount(),
            'canceled' => $this->getCanceledReservationsCount()
        ]);
    }

    /**
     *
     *  Current [ credit, collected, debit, refunded ]
     * Revenue [This Month, Last Month, This Year]
     *
     */
    public function getCredit() : float
    {
        $businessId = request()->route('businessId');
        $credit = Invoice::where('business_id', $businessId)
            ->where('type', PaymentConstants::INVOICE_CREDIT)
            ->sum('amount');
        return $credit;
    }

    public function getCollected() : float
    {
        $businessId = request()->route('businessId');
        $paid = Invoice::where('business_id', $businessId)
            ->where('status', PaymentConstants::INVOICE_PAID)
            ->where('type', PaymentConstants::INVOICE_CREDIT)
            ->sum('amount');
        return $paid;
    }

    public function getDebit() : float
    {
        $businessId = request()->route('businessId');
        $debit = Invoice::where('business_id', $businessId)
            ->where('type', PaymentConstants::INVOICE_DEBIT)
            ->sum('amount');
        return $debit;
    }

    public function getRefunded() : float
    {
        $businessId = request()->route('businessId');
        $refunded = Invoice::where('business_id', $businessId)
            ->where('status', PaymentConstants::INVOICE_DEBIT)
            ->sum('amount');
        return $refunded;
    }

    public function getTotalRevenue() : float
    {
        return $this->getCollected() - $this->getRefunded();
    }

    public function getRevenueByMonth($month = null)
    {
        $businessId = request()->route('businessId');
        $credit = Invoice::where('business_id', $businessId)
            ->where('status', PaymentConstants::INVOICE_PAID)
            ->where('type', PaymentConstants::INVOICE_CREDIT)
            ->whereMonth('created_at', $month ?? now()->month)
            ->sum('amount');
        $debit = Invoice::where('business_id', $businessId)
            ->where('status', PaymentConstants::INVOICE_PAID)
            ->where('type', PaymentConstants::INVOICE_DEBIT)
            ->whereMonth('created_at', $month ?? now()->month)
            ->sum('amount');
        return $credit - $debit;
    }

    public function getRevenueByYear($year = null)
    {
        $businessId = request()->route('businessId');
        $credit = Invoice::where('business_id', $businessId)
            ->where('status', PaymentConstants::INVOICE_PAID)
            ->where('type', PaymentConstants::INVOICE_CREDIT)
            ->whereYear('created_at', $year ?? now()->year)
            ->sum('amount');
        $debit = Invoice::where('business_id', $businessId)
            ->where('status', PaymentConstants::INVOICE_PAID)
            ->where('type', PaymentConstants::INVOICE_DEBIT)
            ->whereYear('created_at', $year ?? now()->year)
            ->sum('amount');
        return $credit - $debit;
    }

    public function getRevenueStatistics()
    {
        return response()->json([
            'current' => [
                'credit' => $this->getCredit(),
                'collected' => $this->getCollected(),
                'debit' => $this->getDebit(),
                'refunded' => $this->getRefunded(),
                'total' => $this->getTotalRevenue()
            ],
            'revenue' => [
                'this_month' => $this->getRevenueByMonth(),
                'last_month' => $this->getRevenueByMonth(now()->subMonth()->month),
                'this_year' => $this->getRevenueByYear()
            ]
        ]);
    }


    /**
     *
     *  Capacity [ last week, current week, 1 week after ]
     *
     *
     */
    public function getCapacity()
    {
        $businessId = request()->route('businessId');

        $now = Carbon::now();

        // Last week
        $lastWeekStart = $now->copy()->subWeek()->startOfWeek(Carbon::SUNDAY);
        $lastWeekEnd = $now->copy()->subWeek()->endOfWeek(Carbon::SATURDAY);

        // Current week
        $thisWeekStart = $now->copy()->startOfWeek(Carbon::SUNDAY);
        $thisWeekEnd = $now->copy()->endOfWeek(Carbon::SATURDAY);

        // Next week
        $nextWeekStart = $now->copy()->addWeek()->startOfWeek(Carbon::SUNDAY);
        $nextWeekEnd = $now->copy()->addWeek()->endOfWeek(Carbon::SATURDAY);

        $reservationsLastWeek = Reservation::where('business_id', $businessId)
            ->where(function ($q) use ($lastWeekStart, $lastWeekEnd) {
                $q->whereBetween('from', [$lastWeekStart, $lastWeekEnd])
                    ->orWhereBetween('to', [$lastWeekStart, $lastWeekEnd])
                    ->orWhere(function ($q2) use ($lastWeekStart, $lastWeekEnd) {
                        $q2->where('from', '<=', $lastWeekStart)
                            ->where('to', '>=', $lastWeekEnd);
                    });
            })->count();

        $reservationsThisWeek = Reservation::where('business_id', $businessId)
            ->where(function ($q) use ($thisWeekStart, $thisWeekEnd) {
                $q->whereBetween('from', [$thisWeekStart, $thisWeekEnd])
                    ->orWhereBetween('to', [$thisWeekStart, $thisWeekEnd])
                    ->orWhere(function ($q2) use ($thisWeekStart, $thisWeekEnd) {
                        $q2->where('from', '<=', $thisWeekStart)
                            ->where('to', '>=', $thisWeekEnd);
                    });
            })->count();

        $reservationsNextWeek = Reservation::where('business_id', $businessId)
            ->where(function ($q) use ($nextWeekStart, $nextWeekEnd) {
                $q->whereBetween('from', [$nextWeekStart, $nextWeekEnd])
                    ->orWhereBetween('to', [$nextWeekStart, $nextWeekEnd])
                    ->orWhere(function ($q2) use ($nextWeekStart, $nextWeekEnd) {
                        $q2->where('from', '<=', $nextWeekStart)
                            ->where('to', '>=', $nextWeekEnd);
                    });
            })->count();

        return [
            'labels' => ['Last Week', 'This Week', 'Next Week'],
            'counts' => [
                $reservationsLastWeek,
                $reservationsThisWeek,
                $reservationsNextWeek
            ]
        ];
    }


    /**
     *  Employees/ Reservations [ last week, current week, next week]
     */
    public function getEmployeesReservationsProgress()
    {
        $businessId = request()->route('businessId');
        $now = Carbon::now();

        $lastWeekStart = $now->copy()->subWeek()->startOfWeek(Carbon::SUNDAY);
        $lastWeekEnd = $now->copy()->subWeek()->endOfWeek(Carbon::SATURDAY);

        $currentWeekStart = $now->copy()->startOfWeek(Carbon::SUNDAY);
        $currentWeekEnd = $now->copy()->endOfWeek(Carbon::SATURDAY);

        $nextWeekStart = $now->copy()->addWeek()->startOfWeek(Carbon::SUNDAY);
        $nextWeekEnd = $now->copy()->addWeeks(2)->endOfWeek(Carbon::SATURDAY);

        $employees = User::where('business_id', $businessId)
            ->where('is_employee', true)
            ->withCount(['followingReservations as last_week_count' => function ($query) use ($lastWeekStart, $lastWeekEnd) {
                $query->whereBetween('from', [$lastWeekStart, $lastWeekEnd]);
            }])
            ->withCount(['followingReservations as current_week_count' => function ($query) use ($currentWeekStart, $currentWeekEnd) {
                $query->whereBetween('from', [$currentWeekStart, $currentWeekEnd]);
            }])
            ->withCount(['followingReservations as next_week_count' => function ($query) use ($nextWeekStart, $nextWeekEnd) {
                $query->whereBetween('from', [$nextWeekStart, $nextWeekEnd]);
            }])
            ->get();

        return [
            'labels' => ['Last Week', 'Current Week', 'Next 2 Weeks'],
            'employees' => $employees->map(function ($employee) {
                return [
                    'name' => $employee->name,
                    'counts' => [
                        $employee->last_week_count,
                        $employee->current_week_count,
                        $employee->next_week_count
                    ]
                ];
            })
        ];
    }

}
