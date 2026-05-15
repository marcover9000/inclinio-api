<?php

namespace App\Modules\Contacts\Domain\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Marca un model de Contacts com a promocionable a client.
 * Comparteix fillable, casts i comportament entre Person i Company.
 */
trait IsClientPromotable
{
    public function initializeIsClientPromotable(): void
    {
        $this->mergeFillable(['is_client', 'became_client_at']);
        $this->mergeCasts([
            'is_client' => 'boolean',
            'became_client_at' => 'datetime',
        ]);
    }

    public function scopeClients(Builder $query): Builder
    {
        return $query->where('is_client', true);
    }

    public function scopeFilterIsClient(Builder $query, ?bool $isClient): Builder
    {
        if ($isClient === null) {
            return $query;
        }

        return $query->where('is_client', $isClient);
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
}
