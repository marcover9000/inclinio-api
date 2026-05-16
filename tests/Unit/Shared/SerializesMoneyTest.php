<?php

use App\Modules\Shared\Domain\ValueObjects\Money;
use App\Modules\Shared\Http\Resources\Concerns\SerializesMoney;

it('serializes a Money to cents/currency/formatted', function () {
    $probe = new class {
        use SerializesMoney;
        public function expose(?Money $m): ?array
        {
            return $this->money($m);
        }
    };

    expect($probe->expose(Money::fromCents(123456, 'EUR')))->toBe([
        'cents' => 123456,
        'currency' => 'EUR',
        'formatted' => '1.234,56 EUR',
    ]);
});

it('serializes null as null', function () {
    $probe = new class {
        use SerializesMoney;
        public function expose(?Money $m): ?array
        {
            return $this->money($m);
        }
    };

    expect($probe->expose(null))->toBeNull();
});
