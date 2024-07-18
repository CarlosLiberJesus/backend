<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_profiles';

    protected $fillable = ['rating', 'rgbd', 'avatar'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function app()
    {
        return $this->belongsTo(Application::class, 'app_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function freguesia()
    {
        return $this->belongsTo(Freguesia::class, 'freguesia_id');
    }

    public function concelho()
    {
        return $this->belongsTo(Concelho::class, 'concelho_id');
    }

    public function distrito()
    {
        return $this->belongsTo(Concelho::class, 'concelho_id');
    }

}
