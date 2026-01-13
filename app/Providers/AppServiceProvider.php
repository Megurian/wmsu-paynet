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
        // Share active school year and semester with all views
        View::composer('*', function ($view) {
            $activeSchoolYear = SchoolYear::with('semesters')
                ->where('is_active', true)
                ->first();

            $view->with('activeSchoolYear', $activeSchoolYear);
        });
    }
}
