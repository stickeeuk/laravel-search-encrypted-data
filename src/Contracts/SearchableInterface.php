<?php

namespace Stickee\LaravelSearchEncryptedData\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Stickee\LaravelSearchEncryptedData\Contracts\FilterInterface;

interface SearchableInterface
{
    /**
     * Get the value hash for a filter
     *
     * @param string $filterName The filter name
     *
     * @return string
     */
    public function getFilterHash(string $filterName): string;

    /**
     * Get the filter object
     *
     * @param string $filterName The filter name
     *
     * @return \Stickee\LaravelSearchEncryptedData\Contracts\FilterInterface
     */
    public function getFilter(string $filterName): FilterInterface;

    /**
     * Get the field (column) for a filter
     *
     * @param string $filterName The filter name
     *
     * @return string
     */
    public function getFilterField(string $filterName): string;

    /**
     * The Laravel relationship for searchables
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function searchables(): MorphMany;

    /**
     * Get the names of all the filters that apply to a field
     *
     * @param string $field The field name
     *
     * @return string[]
     */
    public function getFiltersForField(string $field): array;

    /**
     * Get the names of all the filters that apply to a field and search value
     *
     * @param string $field The field name
     * @param string $searchValue The search string
     *
     * @return string[]
     */
    public function getApplicableFiltersForField(string $field, string $searchValue): array;

    /**
     * Get the Eloquent model IDs where the field matches the search value
     *
     * @param string $filterName The field name
     * @param string $searchValue The search string
     *
     * @return array
     */
    public function getMatchingIds(string $field, string $searchValue): array;

    /**
     * Apply the search to a query as a Laravel scope
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query to add the scope to
     * @param string $field The field name
     * @param string $searchValue The search string
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithSearchable(Builder $query, string $field, string $searchValue): Builder;
}
