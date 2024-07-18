<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoles extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_roles';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }


}
