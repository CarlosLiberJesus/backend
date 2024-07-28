<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid', 'email', 'name', 'app_id', 'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setAppIdAttribute($value)
    {
        if (!$this->app_id) {
            $app = Application::where('uuid', $value)->first();
            if ($app) {
                $this->attributes['app_id'] = $app->id;
            }
        }
    }

    public function apps() {
        return $this->belongsTo(Application::class, 'app_id');
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'user_id');
    }

    // TODO: add roles from how can access
    public function detail()
    {
        return $this->hasOne(UserDetail::class, 'user_id');
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function roles()
    {
        return $this->hasMany(UserRoles::class, 'user_id');
    }

    public function permissions()
    {
        return $this->hasMany(UserPermissions::class, 'user_id');
    }
}
