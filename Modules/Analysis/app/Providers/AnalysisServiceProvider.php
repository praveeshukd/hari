<?php

namespace Modules\Analysis\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Analysis\Console\Commands\DailyFailSummaryCommand;
use Modules\Analysis\Console\Commands\EncryptExistingAnalysesCommand;
use Modules\Analysis\Events\AnalysesImported;
use Modules\Analysis\Listeners\LogAnalysesImported;
use Modules\Analysis\Models\Analysis;
use Modules\Analysis\Policies\AnalysisPolicy;
use Modules\Analysis\Repositories\AnalysisRepositoryInterface;
use Modules\Analysis\Repositories\EloquentAnalysisRepository;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class AnalysisServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Analysis';

    protected string $nameLower = 'analysis';

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        Gate::policy(Analysis::class, AnalysisPolicy::class);
        Event::listen(AnalysesImported::class, LogAnalysesImported::class);

        // Keep using resources/views/analysis + dashboard from the app shell.
        // Also expose module copies as analysis::* for gradual migration.
        $moduleAnalysisViews = module_path($this->name, 'resources/views/analysis');
        if (is_dir($moduleAnalysisViews)) {
            $this->loadViewsFrom($moduleAnalysisViews, 'analysis');
        }
    }

    public function register(): void
    {
        $this->app->bind(AnalysisRepositoryInterface::class, EloquentAnalysisRepository::class);

        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerCommands(): void
    {
        $this->commands([
            EncryptExistingAnalysesCommand::class,
            DailyFailSummaryCommand::class,
        ]);
    }

    protected function registerCommandSchedules(): void
    {
        //
    }

    protected function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    protected function registerConfig(): void
    {
        $analysisConfig = module_path($this->name, 'config/analysis.php');
        if (is_file($analysisConfig)) {
            $this->mergeConfigFrom($analysisConfig, 'analysis');
        }

        $relativeConfigPath = config('modules.paths.generator.config.path');
        $configPath = module_path($this->name, $relativeConfigPath);

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $relativePath = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    if ($relativePath === 'analysis.php') {
                        continue;
                    }
                    $configKey = $this->nameLower.'.'.str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $relativePath);
                    $key = ($relativePath === 'config.php') ? $this->nameLower : $configKey;

                    $this->publishes([$file->getPathname() => config_path($relativePath)], 'config');
                    $this->mergeConfigFrom($file->getPathname(), $key);
                }
            }
        }
    }

    protected function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);
        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        $componentNamespace = $this->module_namespace($this->name, $this->app_path(config('modules.paths.generator.component-class.path')));
        Blade::componentNamespace($componentNamespace, $this->nameLower);
    }

    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}
