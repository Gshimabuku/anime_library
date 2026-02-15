<?php

namespace App\Providers;

use App\Services\AnimeTitleService;
use App\Services\DashboardService;
use App\Services\Impl\AnimeTitleServiceImpl;
use App\Services\Impl\DashboardServiceImpl;
use App\Services\Impl\MemberServiceImpl;
use App\Services\Impl\PlatformServiceImpl;
use App\Services\MemberService;
use App\Services\PlatformService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DashboardService::class, DashboardServiceImpl::class);
        $this->app->bind(MemberService::class, MemberServiceImpl::class);
        $this->app->bind(AnimeTitleService::class, AnimeTitleServiceImpl::class);
        $this->app->bind(PlatformService::class, PlatformServiceImpl::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
