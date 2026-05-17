<?php

namespace App\Modules\Projects\Domain\Models;

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Shared\Domain\ValueObjects\Money;
use App\Modules\Shared\Infrastructure\Casts\MoneyCast;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Projecte (context d'execució/entrega). Client = Company/Person amb
 * is_client (Contacts) o cap si is_internal. NO té source_lead_id (el
 * vincle a lead viu a HoursPack). Spec §4/§5.
 */
class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name', 'status', 'is_internal',
        'client_company_id', 'client_person_id',
        'shadow_rate_override', 'started_at', 'due_at',
    ];

    protected $casts = [
        'status' => ProjectStatus::class,
        'is_internal' => 'boolean',
        'shadow_rate_override' => MoneyCast::class,
        'started_at' => 'date',
        'due_at' => 'date',
    ];

    public function clientCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'client_company_id');
    }

    public function clientPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'client_person_id');
    }

    public function hoursPacks(): HasMany
    {
        return $this->hasMany(HoursPack::class);
    }

    public function scopeOfStatus(Builder $q, array $statuses): Builder
    {
        return $q->whereIn('status', $statuses);
    }

    public function scopeInternal(Builder $q): Builder
    {
        return $q->where('is_internal', true);
    }

    public function scopeForClientCompany(Builder $q, int $companyId): Builder
    {
        return $q->where('client_company_id', $companyId);
    }

    public function scopeForClientPerson(Builder $q, int $personId): Builder
    {
        return $q->where('client_person_id', $personId);
    }

    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where('name', 'like', "%{$term}%");
    }

    /**
     * Preu total venut = suma dels HoursPacks. Sense packs → 0 EUR.
     * (Cost/marge real necessiten TimeEntry + tarifa-ombra → Fase 3b.)
     */
    public function totalPrice(): Money
    {
        $packs = $this->hoursPacks;
        if ($packs->isEmpty()) {
            return Money::zero();
        }

        return $packs->reduce(
            fn (Money $carry, HoursPack $pack) => $carry->add($pack->price),
            Money::zero($packs->first()->price->currency),
        );
    }

    /** Hores pressupostades = suma de les hores de tots els packs. */
    public function budgetedHours(): int
    {
        return (int) $this->hoursPacks->sum('hours');
    }

    protected static function newFactory(): ProjectFactory
    {
        return ProjectFactory::new();
    }
}
