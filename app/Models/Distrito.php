<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'distritos';

    protected $fillable = [
        'descriptions', 'name',
    ];

    public function concelhos()
    {
        return $this->hasMany(Concelho::class);
    }

}
