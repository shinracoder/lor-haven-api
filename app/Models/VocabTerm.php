<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VocabTerm extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vocab_terms';

    protected $fillable = [
        'nameRef',
        'version',
        'locale',
    ];
}
