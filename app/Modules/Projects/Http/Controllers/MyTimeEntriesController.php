<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Domain\Models\TimeEntry;
use App\Modules\Projects\Http\Requests\IndexMyTimeEntriesRequest;
use App\Modules\Projects\Http\Resources\TimeEntryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MyTimeEntriesController extends Controller
{
    public function index(IndexMyTimeEntriesRequest $request): AnonymousResourceCollection
    {
        $v = $request->validated();

        $entries = TimeEntry::query()
            ->where('user_id', $request->user()->id)
            ->when($v['from'] ?? null, fn ($q, $from) => $q->whereDate('worked_on', '>=', $from))
            ->when($v['to'] ?? null, fn ($q, $to) => $q->whereDate('worked_on', '<=', $to))
            ->when($v['project_id'] ?? null, fn ($q, $pid) => $q->where('project_id', $pid))
            ->when($v['task_id'] ?? null, fn ($q, $tid) => $q->where('task_id', $tid))
            ->with(['project', 'task'])
            ->orderByDesc('worked_on')
            ->orderByDesc('id')
            ->get();

        return TimeEntryResource::collection($entries);
    }
}
