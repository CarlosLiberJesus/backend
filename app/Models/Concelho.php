<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Concelho extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'concelhos';

    protected $fillable = [
        'descriptions', 'name',
    ];

    public function distrito()
    {
        return $this->belongsTo(Distrito::class, 'distrito_id');
    }

    public function freguesias()
    {
        return $this->hasMany(Freguesia::class, 'concelho_id');
    }
}
