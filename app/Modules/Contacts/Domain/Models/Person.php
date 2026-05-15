<?php

namespace App\Modules\Contacts\Domain\Models;

use App\Modules\Contacts\Domain\Concerns\IsClientPromotable;
use Database\Factories\PersonFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use HasFactory;
    use IsClientPromotable;
    use SoftDeletes;

    protected $table = 'people';

    protected $fillable = [
        'company_id', 'first_name', 'last_name', 'email', 'phone', 'position',
    ];

    protected $appends = ['full_name'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Leads attached to this Person. Cross-module reference to the Crm
     * module — same one-way Contacts ← Crm coupling we use elsewhere
     * (e.g., CreateLead in Crm depends on Contacts Actions).
     */
    public function leads(): HasMany
    {
        return $this->hasMany(\App\Modules\Crm\Domain\Models\Lead::class);
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->first_name . ' ' . ($this->last_name ?? '')),
        );
    }

    protected static function newFactory(): PersonFactory
    {
        return PersonFactory::new();
    }
}
