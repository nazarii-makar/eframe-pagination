<?php

namespace EFrame\Pagination;

use Illuminate\Support\ServiceProvider;

class PaginationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'pagination');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/resources/views' => $this->app->resourcePath('views/vendor/pagination'),
            ], 'eframe-pagination');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Paginator::viewFactoryResolver(function () {
            return $this->app['view'];
        });

        Paginator::currentPathResolver(function () {
            return $this->app['request']->url();
        });

        Paginator::currentPageResolver(function ($pageName = 'page') {
            $page = $this->app['request']->input($pageName);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }

            return 1;
        });

        Paginator::currentLetResolver(function ($letName = 'let') {
            $let = $this->app['request']->input($letName);

            if (filter_var($let, FILTER_VALIDATE_INT) !== false && (int) $let >= 1) {
                return (int) $let;
            }

            return 0;
        });

        Paginator::currentLimitResolver(function ($limitName = 'limit') {
            $limit = $this->app['request']->input($limitName);

            if (filter_var($limit, FILTER_VALIDATE_INT) !== false && (int) $limit >= 0) {
                return (int) $limit;
            }

            return null;
        });
    }
}
