<?php

namespace App\Modules\Shared\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Domain\Settings;
use App\Modules\Shared\Domain\ValueObjects\Money;
use App\Modules\Shared\Http\Requests\UpdateSettingsRequest;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(['data' => $this->payload()]);
    }

    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        Settings::setShadowRate(Money::fromCents(
            (int) $request->validated()['shadow_rate_cents'],
            $request->validated()['currency'],
        ));

        return response()->json(['data' => $this->payload()]);
    }

    private function payload(): array
    {
        $rate = Settings::shadowRate();

        return ['shadow_rate' => ['cents' => $rate->amountCents, 'currency' => $rate->currency, 'formatted' => $rate->format()]];
    }
}
