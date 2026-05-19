<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Domain\Dashboard\TimeDashboard;
use App\Modules\Projects\Http\Requests\DashboardTimeRequest;
use App\Modules\Projects\Http\Resources\DashboardTimeResource;

class DashboardTimeController extends Controller
{
    public function index(DashboardTimeRequest $request): DashboardTimeResource
    {
        $v = $request->validated();

        return new DashboardTimeResource(
            (new TimeDashboard($v['from'] ?? null, $v['to'] ?? null))->result(),
        );
    }
}
