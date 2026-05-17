<?php

namespace App\Modules\Projects\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use Database\Factories\TimeEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeEntry extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['project_id', 'task_id', 'user_id', 'worked_on', 'minutes', 'description'];

    protected $casts = [
        'worked_on' => 'date',
        'minutes' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): TimeEntryFactory
    {
        return TimeEntryFactory::new();
    }
}
