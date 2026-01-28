<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Models\SchoolYear;

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
            $organization = null;
            $currentCollege = null;

            if ($user) {
                if (in_array($user->role, ['university_org', 'college_org'])) {
                    $organization = $user->organization;
                }

                if (in_array($user->role, ['college', 'student_coordinator', 'adviser'])) {
                    $currentCollege = $user->college;
                }
            }

            $view->with([
                'latestSchoolYear' => $latestSchoolYear,
                'organization'     => $organization,
                'currentCollege'   => $currentCollege,
            ]);
        });
    }

}
