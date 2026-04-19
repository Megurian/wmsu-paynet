<?php

namespace App\Providers;

use App\Models\PromissoryNote;
use App\Observers\PromissoryNoteObserver;
use Illuminate\Support\Facades\URL;
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $reminderDays = (int) config('app.promissory_note_reminder_days_before_due', 7);
        if ($reminderDays <= 0) {
            throw new \RuntimeException('PROMISSORY_NOTE_REMINDER_DAYS_BEFORE_DUE must be a positive integer.');
        }

        PromissoryNote::observe(PromissoryNoteObserver::class);

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

                if (in_array($user->role, ['college', 'student_coordinator', 'adviser', 'assessor'])) {
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
