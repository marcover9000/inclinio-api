<?php

namespace App\Modules\Crm\Domain\Models;

use App\Modules\Identity\Domain\Models\User;
use Database\Factories\LeadNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadNote extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['lead_id', 'author_id', 'body'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected static function newFactory(): LeadNoteFactory
    {
        return LeadNoteFactory::new();
    }
}
