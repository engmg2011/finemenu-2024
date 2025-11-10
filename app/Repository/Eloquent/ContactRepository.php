<?php

namespace App\Repository\Eloquent;


use App\Models\Contact;
use App\Repository\ContactRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class ContactRepository extends BaseRepository implements ContactRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Contact $model
     */
    public function __construct(Contact $model) {
        parent::__construct($model);
    }


    public function process(array $data): array
    {
        return array_only($data, ["key", "media", "value", "contactable_type", "contactable_id"]);
    }

    public function createModel(array $data)
    {
        return $this->model->create($this->process($data));
    }

    public function updateModel($id, array $data): Model
    {
        return tap($this->model->find($id))
            ->update($this->process($data));
    }

    public function listModel()
    {
        return Contact::orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function get(int $id)
    {
        return Contact::find($id);
    }
}
