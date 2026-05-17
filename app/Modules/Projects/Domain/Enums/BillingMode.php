<?php

namespace App\Modules\Projects\Domain\Enums;

/**
 * Mode de facturació d'un pack/venda:
 *  - Fixed  → preu tancat total (hores estimades opcionals o cap).
 *  - Hourly → hores × tarifa €/h (tarifa entrada al pack mateix; el
 *    total es calcula i es guarda). Spec Fase 3a (reformulació billing).
 */
enum BillingMode: string
{
    case Fixed = 'fixed';
    case Hourly = 'hourly';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Preu tancat',
            self::Hourly => 'Per hores',
        };
    }
}
