<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = ['uuid', 'slug', 'icon', 'name', 'description'];

    public function logs()
    {
        return $this->hasMany(Log::class, 'app_id');
    }

    public function profiles()
    {
        return $this->hasMany(UserProfile::class, 'app_id');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'app_id');
    }

    public function users()
    {
        return $this->hasManyThrough(
            User::class,
            UserProfile::class,
            'app_id', // Foreign key on the UserProfiles table...
            'id', // Foreign key on the Users table...
            'id', // Local key on the Applications table...
            'user_id' // Local key on the UserProfiles table...
        );
    }
}

