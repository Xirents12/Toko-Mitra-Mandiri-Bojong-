<?php

namespace App\Providers;

use App\Models\PurchaseOrder;
use App\Policies\PurchaseOrderPolicy;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
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
        // Registrasi policy Purchase Order (RBAC)
        Gate::policy(PurchaseOrder::class, PurchaseOrderPolicy::class);

        // Tanggal & grafik dalam Bahasa Indonesia di seluruh aplikasi
        Date::setLocale('id');
        \Carbon\Carbon::setLocale('id');
    }
}
