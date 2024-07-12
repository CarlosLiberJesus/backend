<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = ['user_id', 'app_id', 'code', 'time', 'url', 'reply', 'params'];

    public function setAppIdAttribute($value)
    {
        $application = Application::where('uuid', $value)->first();

        if ($application) {
            $this->attributes['app_id'] = $application->id;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function app()
    {
        return $this->belongsTo(Application::class, 'app_id');
    }
}
