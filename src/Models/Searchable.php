<?php

namespace Stickee\LaravelSearchEncryptedData\Models;

use Illuminate\Database\Eloquent\Model;

class Searchable extends Model
{
    /**
     * Laravel fillable properties
     *
     * @var string[] $fillable
     */
    protected $fillable = [
        'searchable_type',
        'searchable_id',
        'filter_name',
        'filter_value',
    ];

    /**
     * Laravel timestamps
     *
     * @var bool $timestamps
     */
    public $timestamps = false;

    /**
     * Searchable relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function searchable()
    {
        return $this->morphTo();
    }
}
