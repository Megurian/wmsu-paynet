<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            self::log('created', $model);
        });

        static::updated(function ($model) {
            self::log('updated', $model);
        });

        static::deleted(function ($model) {
            self::log('deleted', $model);
        });
    }

    protected static function log($action, $model)
    {
        if (!Auth::check()) return;

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => ucfirst(class_basename($model)) . " {$action}",
            'description' => ucfirst(class_basename($model)) . " {$action}",
            'student_id' => $model instanceof \App\Models\Student ? $model->id : null,
            'meta' => [
                'model' => get_class($model),
                'model_id' => $model->id,
                'attributes' => $model->getAttributes(),
            ],
        ]);
    }
}