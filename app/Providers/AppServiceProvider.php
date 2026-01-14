<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use App\Models\SchoolYear;
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
        View::composer('*', function ($view) {
            $latestSchoolYear = SchoolYear::with('semesters')
                ->where('is_active', true)
                ->latest('sy_start')
                ->first();
            $view->with('latestSchoolYear', $latestSchoolYear);
        });
    }

    
}
