<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpellSpeed extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spell_speeds';

    protected $fillable = [
        'nameRef',
        'version',
        'locale',
    ];
}
