<?php


namespace App\Actions;

use App\Constants\SettingConstants;
use App\Constants\SubscriptionStatuses;
use App\Models\DietPlan;
use App\Models\DietPlanSubscription;
use App\Models\Item;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Repository\Eloquent\DietPlanSubscriptionRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Psy\Util\Json;

class DietPlanSubscriptionAction
{
    /**
     * Validate meals with Plan
     *
     * diet_plan_id => id of plan to subscribe
     * * Get the restaurant, and it's weekend for validating the dates
     * start_date for subscription  => start from
     * data['selected_meals'] => [day:mealId] like
     * [
     *      '16-10-2022': 112,
     *      '18-10-2022': 114
     * ]
     */
    public function __construct(private DietPlanSubscriptionRepository $repository,
                                private LocaleAction                   $localeAction)
    {
    }

    public function process(array $data): array
    {
        return array_only($data, ['creator_id', 'user_id', 'restaurant_id',
            'status', 'selected_meals', 'diet_plan_id']);
    }

    public function update($id, array $data): Model
    {
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
        if (isset($data['locales']))
            $this->localeAction->updateLocales($model, $data['locales']);
        return $model;
    }

    private function paymentCheck()
    {
        $subscription = DietPlanSubscription::with(['plan', 'user'])->
        where('reference_id', response()->get('referenceId'))->first();

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
        return $this->repository->delete($id);
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
         * Subscription should have status ['pending','active']
         * Subscription should have payment_status ['unpaid', 'paid']
         * Subscription should have type ['recurring' , 'one-time']
         */
        $this->checkValidPlan($data);
        $this->checkValidDates($data);
        $this->checkValidMeals($data);

        $model = $this->repository->create($this->process($data));
        if (isset($data['locales']))
            $this->localeAction->createLocale($model, $data['locales']);
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
        $restaurant = Restaurant::find($data['restaurant_id']);
        $selections = json_decode($data['selected_meals']);
        $selectedDays = [];
        $workDays = $restaurant->settings
            ->where('key', SettingConstants::Keys['WORK_DAYS'])
            ->first()?->data;
        if(!is_null($selections) && !is_null($workDays)){
            foreach ($selections as $date => $mealId){
                $dayName = Carbon::parse($date)->dayName;
                if(!in_array($dayName , $workDays))
                    throw new Exception("Selected days not match the work days");
                if(!in_array($dayName , $selectedDays))
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
        $selections = json_decode($data['selected_meals']);
        if(!is_null($selections)){
            $mealIds = [];
            foreach ($selections as $mealId)
                $mealIds = array_merge($mealIds , [$mealId]);

            $countExist = $plan->items->whereIn('id', $mealIds)->count();
            if($countExist < count($mealIds))
                throw new \Exception('Wrong meals');
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
