<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cards';

    protected $fillable = [
        'cardCode',
        'version',
        'locale',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'associatedCards' => 'array',
        'associatedCardRefs' => 'array',
        'assets' => 'array',
        'keywords' => 'array',
        'keywordRefs' => 'array',
        'subtypes' => 'array',
    ];
}
