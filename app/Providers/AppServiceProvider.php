<?php

namespace App\Providers;

use App\Models\PromissoryNote;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\SystemSetting;
use App\Observers\PromissoryNoteObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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

        try {
            if (Schema::hasTable('system_settings')) {
                $settings = SystemSetting::getKeyValuePairs([
                    'mail_default',
                    'mail_host',
                    'mail_port',
                    'mail_username',
                    'mail_password',
                    'mail_encryption',
                    'mail_from_address',
                    'mail_from_name',
                ]);

                if (! empty($settings)) {
                    if (filled($settings['mail_default'] ?? null)) {
                        config(['mail.default' => $settings['mail_default']]);
                    }

                    if (filled($settings['mail_host'])) {
                        config(['mail.mailers.smtp.host' => $settings['mail_host']]);
                    }

                    if (filled($settings['mail_port'])) {
                        config(['mail.mailers.smtp.port' => $settings['mail_port']]);
                    }

                    if (filled($settings['mail_username'])) {
                        config(['mail.mailers.smtp.username' => $settings['mail_username']]);
                    }

                    if (filled($settings['mail_password'])) {
                        config(['mail.mailers.smtp.password' => $settings['mail_password']]);
                    }

                    if (isset($settings['mail_encryption'])) {
                        config(['mail.mailers.smtp.encryption' => $settings['mail_encryption'] ?: null]);
                    }

                    if (filled($settings['mail_from_address'])) {
                        config(['mail.from.address' => $settings['mail_from_address']]);
                    }

                    if (filled($settings['mail_from_name'])) {
                        config(['mail.from.name' => $settings['mail_from_name']]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore if the DB is not available during migrations or first install.
        }

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
