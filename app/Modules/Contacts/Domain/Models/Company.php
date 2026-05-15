<?php

namespace App\Modules\Contacts\Domain\Models;

use App\Modules\Contacts\Domain\Concerns\IsClientPromotable;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory;
    use IsClientPromotable;
    use SoftDeletes;

    protected $table = 'companies';

    protected $fillable = [
        'name', 'vat', 'website', 'address', 'notes',
    ];

    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    /**
     * Leads attached to this Company. Cross-module reference to the Crm
     * module — same one-way Contacts ← Crm coupling we use elsewhere
     * (e.g., CreateLead in Crm depends on Contacts Actions).
     */
    public function leads(): HasMany
    {
        return $this->hasMany(\App\Modules\Crm\Domain\Models\Lead::class);
    }

    protected static function newFactory(): CompanyFactory
    {
        return CompanyFactory::new();
    }
}
