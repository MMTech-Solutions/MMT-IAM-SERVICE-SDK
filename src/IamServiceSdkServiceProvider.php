<?php

declare(strict_types=1);

namespace Mmt\IamServiceSdk;

use Illuminate\Support\ServiceProvider;
use Mmt\IamServiceSdk\Domains\Auth\AuthService;
use Mmt\IamServiceSdk\Domains\Auth\Contracts\AuthServiceInterface;
use Mmt\IamServiceSdk\Domains\Health\Contracts\HealthServiceInterface;
use Mmt\IamServiceSdk\Domains\Health\HealthService;
use Mmt\IamServiceSdk\Domains\IbSettings\Contracts\IbSettingsServiceInterface;
use Mmt\IamServiceSdk\Domains\IbSettings\IbSettingsService;
use Mmt\IamServiceSdk\Domains\ReferralNetwork\Contracts\ReferralNetworkServiceInterface;
use Mmt\IamServiceSdk\Domains\ReferralNetwork\ReferralNetworkService;
use Mmt\IamServiceSdk\Domains\Users\Contracts\UserServiceInterface;
use Mmt\IamServiceSdk\Domains\Users\UserService;
use Mmt\IamServiceSdk\TransportDrivers\Contracts\TransportInterface;
use Mmt\IamServiceSdk\TransportDrivers\Drivers\Http\IamHttpClient;

class IamServiceSdkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-iam-service-sdk.php',
            'laravel-iam-service-sdk',
        );

        $this->app->singleton(TransportInterface::class, IamHttpClient::class);

        $this->app->singleton(IamService::class, function ($app): IamService {
            return new IamService(
                transport: $app->make(TransportInterface::class),
            );
        });

        $this->registerDomainBindings();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/laravel-iam-service-sdk.php' => config_path('laravel-iam-service-sdk.php'),
            ], 'laravel-iam-service-sdk-config');
        }
    }

    private function registerDomainBindings(): void
    {
        $this->app->bind(ReferralNetworkServiceInterface::class, fn ($app) => $app->make(IamService::class)->referralNetwork());
        $this->app->bind(IbSettingsServiceInterface::class, fn ($app) => $app->make(IamService::class)->ibSettings());
        $this->app->bind(AuthServiceInterface::class, fn ($app) => $app->make(IamService::class)->auth());
        $this->app->bind(UserServiceInterface::class, fn ($app) => $app->make(IamService::class)->users());
        $this->app->bind(HealthServiceInterface::class, fn ($app) => $app->make(IamService::class)->health());

        $this->app->bind(ReferralNetworkService::class, ReferralNetworkServiceInterface::class);
        $this->app->bind(IbSettingsService::class, IbSettingsServiceInterface::class);
        $this->app->bind(AuthService::class, AuthServiceInterface::class);
        $this->app->bind(UserService::class, UserServiceInterface::class);
        $this->app->bind(HealthService::class, HealthServiceInterface::class);
    }
}
