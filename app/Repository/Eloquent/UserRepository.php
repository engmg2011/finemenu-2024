<?php

namespace App\Repository\Eloquent;


use App\Models\User;
use App\Repository\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    const LoginUserRelations = ['restaurants.locales',
        'restaurants.media',
        'restaurants.branches.locales',
        'restaurants.branches.media',
        'settings'];

    public function __construct(User $model) {
        parent::__construct($model);
    }



}
