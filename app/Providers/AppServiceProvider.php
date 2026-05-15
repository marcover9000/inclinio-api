<?php

namespace App\Providers;

use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Events\LeadConverted;
use App\Modules\Crm\Domain\Observers\PersonObserver;
use App\Modules\Crm\Infrastructure\Listeners\MarkPersonAsClientOnConversion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(LeadConverted::class, MarkPersonAsClientOnConversion::class);
        Person::observe(PersonObserver::class);

        // Pagina llegint `per_page` de la request, amb límit màxim.
        Builder::macro('paginateFromRequest', function (int $default = 20, int $max = 100) {
            return $this->paginate(min((int) request()->query('per_page', $default), $max));
        });
    }
}
