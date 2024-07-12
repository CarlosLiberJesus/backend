<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['uuid', 'name', 'color', 'description'];

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'role_id');
    }
}
