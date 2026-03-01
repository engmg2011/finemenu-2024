<?php

namespace App\Services;

use App\Models\Audit;

class AuditService
{
    public static function log($service, $service_id, $message, $business_id = null, $branch_id = null, $data = null): void
    {
        Audit::create([
            'service_type' => $service,
            'service_id' => $service_id,
            'message' => $message,
            'request' => request()->all(),
            'data' => $data,
            'user_id' => auth()->id(),
            'business_id' => $business_id,
            'branch_id' => $branch_id,
        ]);
    }

    public function backup($businessId)
    {
        $audits = Audit::where('business_id', $businessId)->get();
        return compact('audits');
    }

}
