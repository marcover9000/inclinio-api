<?php

namespace App\Modules\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Crm\Application\Actions\TransitionLeadStatus;
use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Exceptions\InvalidLeadTransition;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Crm\Http\Requests\TransitionLeadStatusRequest;
use App\Modules\Crm\Http\Resources\LeadResource;

class LeadStatusController extends Controller
{
    public function __invoke(
        TransitionLeadStatusRequest $request,
        Lead $lead,
        TransitionLeadStatus $action,
    ): LeadResource {
        try {
            $updated = $action($lead, LeadStatus::from($request->validated('status')));
        } catch (InvalidLeadTransition $e) {
            abort(422, $e->getMessage());
        }

        return LeadResource::make($updated->load(['person.company', 'company']));
    }
}
