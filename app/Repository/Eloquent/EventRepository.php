<?php

namespace App\Repository\Eloquent;



use App\Models\Event;
use App\Repository\EventRepositoryInterface;

class EventRepository extends BaseRepository implements EventRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Event $model
     */
    public function __construct(Event $model) {
        parent::__construct($model);
    }

    public function list()
    {
        return $this->model::with(['locales','media'])->orderByDesc('id')->paginate(request('per-page', 15));
    }

}
