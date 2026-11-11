<?php

namespace Modules\Analysis\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Analysis\Events\AnalysesImported;
use Modules\Analysis\Listeners\LogAnalysesImported;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AnalysesImported::class => [
            LogAnalysesImported::class,
        ],
    ];

    protected static $shouldDiscoverEvents = false;

    protected function configureEmailVerification(): void
    {
        //
    }
}
