<?php

namespace App\Providers;

use App\Services\Ldap\AnnuaireLdap;
use App\Services\Ldap\FakeAnnuaireLdap;
use App\Services\Ldap\NativeAnnuaireLdap;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AnnuaireLdap::class, function ($app) {
            $config = $app['config']['ldap'];

            // L'annuaire simulé n'est jamais utilisable en production.
            if ($config['driver'] === 'fake' && ! $app->environment('production')) {
                return new FakeAnnuaireLdap($config['fake_accounts']);
            }

            return new NativeAnnuaireLdap($config);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
