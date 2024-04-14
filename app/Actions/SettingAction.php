<?php


namespace App\Actions;

use App\Models\Setting;
use App\Repository\Eloquent\SettingRepository;
use Illuminate\Database\Eloquent\Model;

class SettingAction
{
    public function __construct(private SettingRepository $repository)
    {
    }


}
