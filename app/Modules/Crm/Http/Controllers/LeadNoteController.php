<?php

namespace App\Modules\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Crm\Application\Actions\AddLeadNote;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Crm\Domain\Models\LeadNote;
use App\Modules\Crm\Http\Requests\StoreLeadNoteRequest;
use App\Modules\Crm\Http\Resources\LeadNoteResource;
use Illuminate\Http\Response;

class LeadNoteController extends Controller
{
    public function store(
        StoreLeadNoteRequest $request,
        Lead $lead,
        AddLeadNote $addNote,
    ): LeadNoteResource {
        $note = $addNote($lead, $request->user(), $request->validated('body'));
        return LeadNoteResource::make($note->load('author'));
    }

    public function destroy(LeadNote $note): Response
    {
        $user = request()->user();
        if ($note->author_id !== $user->id && !$user->hasRole('admin')) {
            abort(403);
        }
        $note->delete();
        return response()->noContent();
    }
}
