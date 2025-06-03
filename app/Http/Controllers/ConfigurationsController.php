<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class ConfigurationsController extends Controller
{
    public function getBusinessConfig($businessId){
        return response()->json(Business::find($businessId)->configurations);
    }

    public function setBusinessConfig(Request $request, $businessId)
    {
        $request->validate(['configurations' => 'required|array']);
        $business = Business::find($businessId);
        $configs = $request->get('configurations') ;
        foreach ($configs as &$config) {
            $business->setConfig($config['key'], $config['value']);
        }
        return $this->getBusinessConfig($businessId);
    }
}
