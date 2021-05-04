<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rarity extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rarities';

    protected $fillable = [
        'nameRef',
        'version',
        'locale',
    ];
}
