<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

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
        // Atur locale Carbon ke Bahasa Indonesia agar translatedFormat()
        // menampilkan nama bulan & hari dalam Bahasa Indonesia (contoh: "Juni", "Senin")
        Carbon::setLocale('id');
        app()->setLocale('id');

        // Gunakan Bootstrap 5 untuk template pagination bawaan Laravel
        Paginator::useBootstrapFive();

        // Paksa skema HTTPS jika diakses lewat Ngrok untuk menghindari Mixed Content (CSS/JS hancur)
        if (str_contains(request()->url(), 'ngrok-free') || request()->header('X-Forwarded-Proto') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
