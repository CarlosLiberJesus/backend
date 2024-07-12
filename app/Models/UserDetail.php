<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_details';

    protected $fillable = ['nif', 'address', 'city', 'postal_code', 'birthday'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
