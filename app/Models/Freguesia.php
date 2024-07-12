<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Freguesia extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'freguesias';

    protected $fillable = [
        'descriptions', 'name',
    ];

    public function distrito()
    {
        return $this->belongsTo(Distrito::class, 'distrito_id');
    }

    public function concelho()
    {
        return $this->belongsTo(Concelho::class, 'concelho_id');
    }
}
