<?php

namespace Stickee\LaravelSearchEncryptedData;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Stickee\LaravelSearchEncryptedData\Contracts\FilterInterface;
use Stickee\LaravelSearchEncryptedData\Contracts\FiltersExecutorInterface;
use Stickee\LaravelSearchEncryptedData\Models\Searchable as SearchableModel;

/**
 */
trait Searchable
{
    /**
     * A cache of filters so we don't have to create a new instance every time
     *
     * @var array $filterCache
     */
    private static $filterCache = [];

    /**
     * Classes using this trait use $searchable to define searchable field
     *
     * @var array $searchable
     */
    // public $searchable = [];

     /**
     * Classes using this trait can override the filters executor by setting
     * the $searchableFiltersExecutor property to a class implementing
     * \Stickee\LaravelSearchEncryptedData\Contracts\FiltersExecutorInterface
     *
     * @var \Stickee\LaravelSearchEncryptedData\Contracts\FiltersExecutorInterface $searchableFiltersExecutor
     */
    // public $searchableFiltersExecutor = \App\MyCustomExecutor::class;

    /**
     * Laravel trait boot method
     */
    public static function bootSearchable(): void
    {
        self::saved(function (self $model) {
            foreach ($model->searchable ?? [] as $filterName => $filterData) {
                // TODO check if dirty
                $model->searchables()->updateOrCreate(
                    [
                        'filter_name' => $filterName,
                        'filter_value' => $model->getFilterHash($filterName),
                    ]
                );
            }
        });

        // TODO test soft deleting
        self::deleting(function (self $model) {
            $model->searchables()->delete();
        });
    }

    /**
     * Get the columns to select for searching a particular field
     *
     * @param string $field The field to search
     *
     * @return string[]
     */
    public static function searchableGetColumns(string $field): array
    {
        return [$field];
    }

    /**
     * Get the value hash for a filter
     *
     * @param string $filterName The filter name
     *
     * @return string
     */
    public function getFilterHash(string $filterName): string
    {
        $filter = $this->getFilter($filterName);
        $field = $this->getFilterField($filterName);

        return $filter->getHash((string)$this->{$field});
    }

    /**
     * Get the filter object and which field it applies to
     *
     * @param string $filterName The filter name
     *
     * @return array
     */
    private function getFilterData(string $filterName): array
    {
        // Use `static::class` in the cache to make subclasses work properly
        if (empty(self::$filterCache[static::class][$filterName])) {
            $filterData = $this->searchable[$filterName];

            $filterClass = array_shift($filterData);
            $filterField = array_shift($filterData);

            if (empty(self::$filterCache[static::class])) {
                self::$filterCache[static::class] = [];
            }

            self::$filterCache[static::class][$filterName] = [
                'field' => $filterField,
                'filter' => new $filterClass(...$filterData),
            ];
        }

        return self::$filterCache[static::class][$filterName];
    }

    /**
     * Get the filter object
     *
     * @param string $filterName The filter name
     *
     * @return \Stickee\LaravelSearchEncryptedData\Contracts\FilterInterface
     */
    public function getFilter(string $filterName): FilterInterface
    {
        return $this->getFilterData($filterName)['filter'];
    }

    /**
     * Get the field (column) for a filter
     *
     * @param string $filterName The filter name
     *
     * @return string
     */
    public function getFilterField(string $filterName): string
    {
        return $this->getFilterData($filterName)['field'];
    }

    /**
     * The Laravel relationship for searchables
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function searchables(): MorphMany
    {
        return $this->morphMany(SearchableModel::class, 'searchable');
    }

    /**
     * Get the names of all the filters that apply to a field
     *
     * @param string $field The field name
     *
     * @return string[]
     */
    public function getFiltersForField(string $field): array
    {
        $filters = [];

        foreach ($this->searchable ?? [] as $filterName => $filterData) {
            if ($this->getFilterField($filterName) === $field) {
                $filters[] = $filterName;
            }
        }

        return $filters;
    }

    /**
     * Get the names of all the filters that apply to a field and search value
     *
     * @param string $filterName The filter name
     * @param string $searchValue The search string
     *
     * @return string[]
     */
    public function getApplicableFiltersForField(string $field, string $searchValue): array
    {
        $filters = [];

        foreach ($this->getFiltersForField($field) as $filterName) {
            if ($this->getFilter($filterName)->appliesTo($searchValue)) {
                $filters[] = $filterName;
            }
        }

        return $filters;
    }

    /**
     * Get the filters executor
     *
     * @return \Stickee\LaravelSearchEncryptedData\Contracts\FiltersExecutorInterface
     */
    private function getFiltersExecutor(): FiltersExecutorInterface
    {
        return app($this->searchableFiltersExecutor ?? config('laravel-search-encrypted-data.default_filters_executor'));
    }

    /**
     * Get the Eloquent model IDs where the field matches the search value
     *
     * @param string $filterName The field name
     * @param string $searchValue The search string
     *
     * @return array
     */
    public function getMatchingIds(string $field, string $searchValue): array
    {
        return $this->getFiltersExecutor()->execute($this, $field, $searchValue);
    }

    /**
     * Apply the search to a query as a Laravel scope
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query to add the scope to
     * @param string $field The field name
     * @param string $searchValue The search string
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithSearchable(Builder $query, string $field, string $searchValue): Builder
    {
        return $query->whereIn('id', $this->getMatchingIds($field, $searchValue));
    }
}
