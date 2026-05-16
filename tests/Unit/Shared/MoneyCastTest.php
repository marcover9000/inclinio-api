<?php

use App\Modules\Shared\Domain\ValueObjects\Money;
use App\Modules\Shared\Infrastructure\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function moneyCastModel(): Model
{
    Schema::dropIfExists('cast_probes');
    Schema::create('cast_probes', function ($t) {
        $t->id();
        $t->integer('price_cents')->nullable();
        $t->string('price_currency', 3)->nullable();
        $t->timestamps();
    });

    return new class extends Model {
        protected $table = 'cast_probes';
        protected $guarded = [];
        protected $casts = ['price' => MoneyCast::class];
    };
}

it('persists a Money to *_cents/*_currency and reads it back', function () {
    $m = moneyCastModel();
    $row = $m->newInstance();
    $row->price = Money::fromCents(2500, 'EUR');
    $row->save();

    $fresh = $m->newQuery()->find($row->id);
    expect($fresh->price)->toBeInstanceOf(Money::class)
        ->and($fresh->price->amountCents)->toBe(2500)
        ->and($fresh->price->currency)->toBe('EUR')
        ->and($fresh->getAttributes()['price_cents'])->toBe(2500);
});

it('round-trips null', function () {
    $m = moneyCastModel();
    $row = $m->newInstance();
    $row->price = null;
    $row->save();

    $fresh = $m->newQuery()->find($row->id);
    expect($fresh->price)->toBeNull()
        ->and($fresh->getAttributes()['price_cents'])->toBeNull()
        ->and($fresh->getAttributes()['price_currency'])->toBeNull();
});

it('throws when set to a non-Money, non-null value', function () {
    $row = moneyCastModel()->newInstance();
    expect(fn () => $row->price = 1000)->toThrow(InvalidArgumentException::class);
});
