<?php


namespace App\Actions;


use App\Models\User;
use App\Repository\Eloquent\UserRepository;

class UserAction
{

    public function __construct(private UserRepository $repository) {
    }

    public function processUser(Array $data) {
        return array_only( $data , ['name', 'email', 'phone','type', 'currency', 'password', 'email_verified_at']);
    }

    public function create(Array $data) {
        return $this->repository->create($this->processUser($data));
    }


    public function menu($user_id)
    {
        return User::with([
            'categories.locales', 'categories.media', 'categories.children.locales',
            'categories.children.media', 'categories.items.locales', 'categories.items.media', 'categories.items.prices.locales',
            'categories.children.items.locales', 'categories.children.items.media', 'categories.children.items.prices.locales',
            'items.locales', 'items.media', 'items.prices.locales'
        ])->find($user_id);
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return User::orderByDesc('id')->paginate(request('per-page', 15));
    }

}
