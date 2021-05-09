<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Card extends Model
{
    use Searchable;

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
     * Get the name of the index associated with the model.
     *
     * @return string
     */
    public function searchableAs(): string
    {
        return 'cards_index';
    }

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

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();

        unset($array['flavorText']);

        // Customize the data array...

        return $array;
    }
}
