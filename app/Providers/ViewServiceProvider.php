<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\SchoolYear;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
       
        View::composer('*', function ($view) {
            $activeSchoolYear = SchoolYear::with('activeSemester')
                ->where('is_active', true)
                ->first();

            $view->with('activeSchoolYear', $activeSchoolYear);
        });
    }
}
