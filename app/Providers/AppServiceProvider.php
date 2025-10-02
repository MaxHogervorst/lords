<?php

namespace App\Providers;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\GroupRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\MemberRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repositories as singletons
        $this->app->singleton(MemberRepository::class, function ($app) {
            return new MemberRepository();
        });

        $this->app->singleton(GroupRepository::class, function ($app) {
            return new GroupRepository();
        });

        $this->app->singleton(OrderRepository::class, function ($app) {
            return new OrderRepository();
        });

        $this->app->singleton(InvoiceRepository::class, function ($app) {
            return new InvoiceRepository();
        });

        $this->app->singleton(ProductRepository::class, function ($app) {
            return new ProductRepository();
        });
    }
}
