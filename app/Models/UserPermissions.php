<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermissions extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_permissions';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

}
