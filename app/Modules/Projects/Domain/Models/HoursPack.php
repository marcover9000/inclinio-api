<?php

namespace App\Modules\Projects\Domain\Models;

use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Projects\Domain\Enums\BillingMode;
use App\Modules\Shared\Infrastructure\Casts\MoneyCast;
use Database\Factories\HoursPackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Bossa d'hores / venda. Sub-domini de facturació AÏLLAT (spec §3/§4).
 * billing_mode:
 *  - Fixed  → preu tancat total; `hours` opcional (estimació), `hourly_rate` null.
 *  - Hourly → `hours` × `hourly_rate` (tarifa €/h entrada al pack mateix);
 *    `price` es calcula i es guarda a l'acció. Mai hores imputades (això és
 *    TimeEntry, Fase 3b).
 */
class HoursPack extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'billing_mode', 'hours', 'price', 'hourly_rate', 'dated_on', 'reason', 'source_lead_id',
    ];

    protected $casts = [
        'billing_mode' => BillingMode::class,
        'hours' => 'integer',
        'price' => MoneyCast::class,
        'hourly_rate' => MoneyCast::class,
        'dated_on' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function sourceLead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'source_lead_id');
    }

    protected static function newFactory(): HoursPackFactory
    {
        return HoursPackFactory::new();
    }
}
