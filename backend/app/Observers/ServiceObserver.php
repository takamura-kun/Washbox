<?php

namespace App\Observers;

use App\Models\Service;
use App\Models\ActivityLog;
use App\Models\DeletedRecord;

class ServiceObserver
{
    public function deleting(Service $service): void
    {
        DeletedRecord::snapshot($service, 'service');
        ActivityLog::log('deleted', "Service \"{$service->name}\" deleted", 'service', null, [
            'name' => $service->name,
        ]);
    }
}
