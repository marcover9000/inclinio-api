<?php

namespace App\Modules\Contacts\Domain\Models;

use Database\Factories\PersonFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'people';

    protected $fillable = [
        'company_id', 'first_name', 'last_name', 'email', 'phone', 'position',
        'is_client', 'became_client_at',
    ];

    protected $casts = [
        'is_client' => 'boolean',
        'became_client_at' => 'datetime',
    ];

    protected $appends = ['full_name'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
