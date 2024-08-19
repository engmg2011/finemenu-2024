<?php

namespace App\Repository\Eloquent;


use App\Models\User;
use App\Repository\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    const LoginUserRelations = ['business.locales',
        'business.media',
        'business.branches.locales',
        'business.branches.media',
        'settings'];

    public function __construct(User $model) {
        parent::__construct($model);
    }



}
