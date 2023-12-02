<?php


namespace App\Actions;

use App\Models\Contact;
use App\Repository\Eloquent\ContactRepository;
use Illuminate\Database\Eloquent\Model;

class ContactAction
{
    private $repository;

    public function __construct(ContactRepository $repository)
    {
        $this->repository = $repository;
    }

    public function process(array $data): array
    {
        return array_only($data, ['user_id', 'media', 'value', 'contactable_type', 'contactable_id']);
    }

    public function create(array $data)
    {
        return $this->repository->create($this->process($data));
    }

    public function update($id, array $data): Model
    {
        return tap($this->repository->find($id))
            ->update($this->process($data));
    }

    public function list()
    {
        return Contact::orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function get(int $id)
    {
        return Contact::find($id);
    }
}
