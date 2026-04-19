<?php

namespace App\Providers;

use App\Models\PromissoryNote;
use App\Observers\PromissoryNoteObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Models\SchoolYear;
use App\Models\Semester;

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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $reminderDays = (int) config('app.promissory_note_reminder_days_before_due', 7);
        if ($reminderDays <= 0) {
            throw new \RuntimeException('PROMISSORY_NOTE_REMINDER_DAYS_BEFORE_DUE must be a positive integer.');
        }

        PromissoryNote::observe(PromissoryNoteObserver::class);
        //SchoolYear::observe(SchoolYearObserver::class);
        //Semester::observe(SemesterObserver::class);

        View::composer('*', function ($view) {

            $latestSchoolYear = SchoolYear::with('semesters')
                ->where('is_active', true)
                ->latest('sy_start')
                ->first();

            $user = Auth::user();

            $organization = null;
            $currentCollege = null;
            if (!$user) {
                $view->with([
                    'latestSchoolYear' => $latestSchoolYear,
                    'organization' => null,
                    'currentCollege' => null,
                ]);

                return;
            }

            if ($user->hasRole('university_org') || $user->hasRole('college_org')) {
                $organization = $user->organization;
            }

            if (
                $user->hasRole('college') ||
                $user->hasRole('student_coordinator') ||
                $user->hasRole('adviser') ||
                $user->hasRole('assessor')
            ) {
                $currentCollege = $user->college;
            }

            $view->with([
                'latestSchoolYear' => $latestSchoolYear,
                'organization'     => $organization,
                'currentCollege'   => $currentCollege,
            ]);
        });
    }

}
