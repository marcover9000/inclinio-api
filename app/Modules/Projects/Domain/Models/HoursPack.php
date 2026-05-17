<?php

namespace App\Modules\Projects\Domain\Models;

use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Shared\Infrastructure\Casts\MoneyCast;
use Database\Factories\HoursPackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Bossa d'hores. Pack #1 = venda inicial; cada ampliació = un pack nou.
 * Sub-domini de facturació AÏLLAT (spec §3/§4). Mai conté hores imputades
 * (això és TimeEntry, Fase 3b); aquí només la venda (hores + preu).
 */
class HoursPack extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'hours', 'price', 'dated_on', 'reason', 'source_lead_id',
    ];

    protected $casts = [
        'hours' => 'integer',
        'price' => MoneyCast::class,
        'dated_on' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Lead d'origen (opcional, informatiu). Referència un sentit
     * Projects → Crm; mai la inversa.
     */
    public function sourceLead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'source_lead_id');
    }

    protected static function newFactory(): HoursPackFactory
    {
        return HoursPackFactory::new();
    }
}
