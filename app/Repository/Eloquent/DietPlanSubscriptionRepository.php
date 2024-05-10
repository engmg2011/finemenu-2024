<?php

namespace App\Repository\Eloquent;

use App\Constants\SubscriptionStatuses;
use App\Models\DietPlan;
use App\Models\DietPlanSubscription;
use App\Models\Subscription;
use App\Repository\DietPlanSubscriptionRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\Repository\SettingRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;

class DietPlanSubscriptionRepository extends BaseRepository implements DietPlanSubscriptionRepositoryInterface
{
    /**
     * Validate meals with Plan
     *
     * diet_plan_id => id of plan to subscribe
     * * Get the restaurant, and it's weekend for validating the dates
     * data['selected_meals'] => [day:mealId] like
     * [  { "day":"22-04-2024", "meal_id": 16 }, ... ]
     */

    public function __construct(DietPlanSubscription               $model,
                                private SettingRepositoryInterface $settingRepository,
                                private LocaleRepository           $localeAction,
                                private OrderRepositoryInterface   $orderRepository)
    {
        parent::__construct($model);
    }


    public function process(array $data): array
    {
        $data['creator_id'] = auth('api')->user()->id;
        $data['user_id'] = request()->get('user_id') ?? auth('api')->user()->id;

        return array_only($data, ['creator_id', 'user_id', 'restaurant_id',
            'status', 'selected_meals', 'diet_plan_id', 'note']);
    }

    public function update($id, array $data): bool
    {
        $model = tap($this->model->find($id))
            ->update($this->process($data));
        if (isset($data['locales']))
            $this->localeAction->setLocales($model, $data['locales']);
        return true;
    }

    private function paymentCheck()
    {
        return true;

        $subscription = DietPlanSubscription::with(['plan', 'user'])->
        where('reference_id', request('referenceId'))->first();

        // If payment succeeded
        $from = Carbon::today()->format('Y-m-d');
        $to = Carbon::now()->add($subscription->package->days, 'days')->format('Y-m-d');
        $subscription->update(['from' => $from, 'to' => $to, 'status' => SubscriptionStatuses::PAID]);

        // Notify user by email and sms
        $subscription->user->notify();

        // If payment failed
        // Notify user

    }


    /**
     * @return mixed
     */
    public function list()
    {
        return Subscription::with('locales')->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function get(int $id)
    {
        return Subscription::with('locales')->find($id);
    }

    public function destroy($id): ?bool
    {
        return $this->delete($id);
    }

    /**
     * @param $data
     * @return Model
     * @throws Exception
     */
    public function create($data): Model
    {
        /**
         * Should validate all meals with the plan ( checkValidData() )
         * Should save all days meals (with meal data at subscription)
         * Subscription should be processed after payment
         * Subscription should have status ['paused','active']
         * Subscription should have payment_status ['pending','paid']
         * Subscription should be 'one-time'
         */
        $this->checkValidPlan($data);
        $this->checkValidDates($data);
        $this->checkValidMeals($data);

        $model = $this->model->create($this->process($data));
        if (isset($data['locales']))
            $this->localeAction->createLocale($model, $data['locales']);
        if ($this->paymentCheck())
            $this->createSubscriptionOrders($data);
        return $model;
    }

    /**
     * @param $data
     * @return void
     * @throws Exception
     */
    private function checkValidPlan($data): void
    {
        $dietPlan = DietPlan::find($data['diet_plan_id']);
        if (is_null($dietPlan))
            throw new Exception("No plan found for the same id");
    }

    /**
     * @param $data
     * @return void
     * @throws Exception
     */
    private function checkValidDates($data): void
    {
        if (!(isset($data['selected_meals']) && count($data['selected_meals'])))
            throw new Exception("You have to choose the meals");

        $selected_meals = $data['selected_meals'];

        $selectedDays = [];
        $workDays = $this->settingRepository->getWorkingDays($data['restaurant_id']);
        if (!is_null($selected_meals) && !is_null($workDays)) {
            foreach ($selected_meals as $selectedMeal) {
                $dayName = Carbon::parse($selectedMeal['day'])->format('D');
                if (!in_array($dayName, $workDays))
                    throw new Exception("Selected days are not matching the working days");
                if (!in_array($dayName, $selectedDays))
                    $selectedDays[] = $dayName;
            }
        }
    }

    /**
     * @param $data
     * @return void
     * @throws Exception
     */
    private function checkValidMeals($data): void
    {
        // check the meals allowed in the selected plan
        $plan = DietPlan::find($data['diet_plan_id']);
        $selectedMeals = $data['selected_meals'];
        if (!is_null($selectedMeals)) {
            $mealIds = [];
            foreach ($selectedMeals as $selectedMeal)
                $mealIds = array_merge($mealIds, [$selectedMeal['meal_id']]);

            $countExist = $plan->items->whereIn('id', $mealIds)->count();
            if ($countExist < count(array_unique($mealIds)))
                throw new \Exception('Wrong meals');
        }
    }

    private function createSubscriptionOrders($data)
    {

        $selectedMeals = $data['selected_meals'];
        if (!is_null($selectedMeals)) {
            foreach ($selectedMeals as &$selectedMeal) {
                $this->orderRepository->create([
                    "note" => $data['note'],
                    "orderable_type" => "App\\Models\\Restaurant",
                    "orderable_id" => $data['restaurant_id'],
                    "scheduled_at" => Carbon::parse($selectedMeal['day']),
                    "status" => 1,
                    "paid" => true,
                    "order_lines" => [
                        [
                            "note" => "",
                            "item_id" => $selectedMeal['meal_id'],
                            "count" => 1,
                            "price_id" => null
                        ],
                    ],
                ]);
            }

        }
    }

    /**
     * @return void
     */
    public function paySubscription()
    {
        /**
         * Change status & payment_status
         * Create scheduled paid orders for all meals after every payment
         * Create scheduled notifications for driver
         */
    }
}
