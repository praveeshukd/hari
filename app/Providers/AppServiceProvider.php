<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Analysis bindings live in Modules\Analysis\Providers\AnalysisServiceProvider.
    }

    public function boot(): void
    {
        //
    }
}
