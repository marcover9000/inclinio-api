<?php

namespace App\Modules\Contacts\Domain\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'companies';

    protected $fillable = [
        'name', 'vat', 'website', 'address', 'notes',
        'is_client', 'became_client_at',
    ];

    protected $casts = [
        'is_client' => 'boolean',
        'became_client_at' => 'datetime',
    ];

    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    public function scopeClients($query)
    {
        return $query->where('is_client', true);
    }

    public function promoteToClient(): void
    {
        if (!$this->is_client) {
            $this->update([
                'is_client' => true,
                'became_client_at' => now(),
            ]);
        }
    }

    protected static function newFactory(): CompanyFactory
    {
        return CompanyFactory::new();
    }
}
