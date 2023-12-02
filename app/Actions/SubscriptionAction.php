<?php


namespace App\Actions;

use App\Constants\SubscriptionStatuses;
use App\Models\Subscription;
use App\Repository\Eloquent\SubscriptionRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SubscriptionAction
{
    public function __construct(private SubscriptionRepository $repository,
                                private LocaleAction           $localeAction)
    {
    }

    public function process(array $data): array
    {
        return array_only($data, ['creator_id', 'user_id', 'status', 'from', 'to', 'package_id']);
    }

    public function create(array $data)
    {
        $model = $this->repository->create($this->process($data));
        if(isset($data['locales']))
            $this->localeAction->createLocale($model, $data['locales']);
        return $model;
    }

    public function update($id, array $data): Model
    {
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
        if(isset($data['locales']))
            $this->localeAction->updateLocales($model, $data['locales']);
        return $model;
    }

    private function paymentCheck()
    {
        $subscription = Subscription::with(['package','user'])->
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

}
