<?php

namespace Stickee\LaravelSearchEncryptedData\Test;

use Illuminate\Database\Eloquent\Model;
use Stickee\LaravelSearchEncryptedData\Contracts\SearchableInterface;
use Stickee\LaravelSearchEncryptedData\Filters\EndsWith;
use Stickee\LaravelSearchEncryptedData\Filters\Equals;
use Stickee\LaravelSearchEncryptedData\Filters\StartsWith;
use Stickee\LaravelSearchEncryptedData\Searchable;

/**
 */
class TestModel extends Model implements SearchableInterface
{
    use Searchable;

    /**
     * Search filters
     *
     * @var array[] $searchable
     */
    public $searchable = [
        'first_name_starts_with_3' => [StartsWith::class, 'first_name'],
        'first_name_starts_with_6' => [StartsWith::class, 'first_name', 6],
        'email_equals' => [Equals::class, 'email'],
    ];

    /**
     * Laravel fillable properties
     *
     * @var string[] $fillable
     */
    public $fillable = [
        'id',
        'first_name',
        'email',
    ];

    /**
     * Laravel model timestamps
     *
     * @var bool $timestamps
     */
    public $timestamps = false;
}
