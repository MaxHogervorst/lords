<?php

namespace App\Providers;

use App\Models\InvoiceGroup;
use App\Models\Product;
use App\Observers\InvoiceGroupObserver;
use App\Observers\ProductObserver;
use App\Repositories\GroupRepository;
use App\Repositories\InvoiceLineRepository;
use App\Repositories\InvoiceProductPriceRepository;
use App\Repositories\InvoiceProductRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\MemberRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        Product::observe(ProductObserver::class);
        InvoiceGroup::observe(InvoiceGroupObserver::class);

        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');

            // Ensure APP_URL is set correctly for URL generation
            // This helps when running behind reverse proxies
            if ($appUrl = config('app.url')) {
                $parsedUrl = parse_url($appUrl);
                if (isset($parsedUrl['host'])) {
                    URL::forceRootUrl($appUrl);
                }
            }
        }
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

        $this->app->singleton(InvoiceProductRepository::class, function ($app) {
            return new InvoiceProductRepository();
        });

        $this->app->singleton(InvoiceProductPriceRepository::class, function ($app) {
            return new InvoiceProductPriceRepository();
        });

        $this->app->singleton(InvoiceLineRepository::class, function ($app) {
            return new InvoiceLineRepository();
        });
    }
}
