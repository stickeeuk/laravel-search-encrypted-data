<?php

namespace Stickee\LaravelSearchEncryptedData\Test;

use Stickee\LaravelSearchEncryptedData\Contracts\SearchableInterface;
use Stickee\LaravelSearchEncryptedData\Filters\EqualsCaseSensitive;
use Stickee\LaravelSearchEncryptedData\Filters\StartsWithCaseSensitive;

/**
 */
class TestModelCaseSensitive extends TestModel
{
    /**
     * Laravel table name
     *
     * @var string $table
     */
    public $table = 'test_models';

    /**
     * Search filters
     *
     * @var array[] $searchable
     */
    public $searchable = [
        'first_name_starts_with_3' => [StartsWithCaseSensitive::class, 'first_name'],
        'first_name_starts_with_6' => [StartsWithCaseSensitive::class, 'first_name', 6],
        'email_equals' => [EqualsCaseSensitive::class, 'email'],
        'computed_starts_with' => [StartsWithCaseSensitive::class, 'computed'],
    ];
}
