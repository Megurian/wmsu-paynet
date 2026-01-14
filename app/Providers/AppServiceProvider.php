<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use App\Models\SchoolYear;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

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

            $user = Auth::user();
            $college = null;

            if ($user && $user->college_id) {
                $college = $user->college; // uses relationship
            }

            $view->with([
                'latestSchoolYear' => $latestSchoolYear,
                'currentCollege' => $college,
            ]);
        });
    }
}
