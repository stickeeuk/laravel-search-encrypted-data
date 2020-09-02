<?php

namespace Stickee\LaravelSearchEncryptedData;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Stickee\LaravelSearchEncryptedData\Contracts\FiltersExecutorInterface;
use Stickee\LaravelSearchEncryptedData\Contracts\SearchableInterface;

/**
 */
class FiltersExecutor implements FiltersExecutorInterface
{
    /**
     * Execute the search
     *
     * @param \Stickee\LaravelSearchEncryptedData\Contracts\SearchableInterface $model An instance of the Eloquent model to search on
     * @param string $field The column to search
     * @param string $searchValue The string to search for
     *
     * @return array The model IDs found
     */
    public function execute(SearchableInterface $model, string $field, string $searchValue): array
    {
        $className = get_class($model);
        $filterNames = $model->getApplicableFiltersForField($field, $searchValue);

        if (empty($filterNames)) {
            return [];
        }

        $query = $className::query();

        foreach ($filterNames as $filterName) {
            $query->whereExists(function ($innerQuery) use ($model, $filterName, $searchValue) {
                $this->applyQuery($model, $filterName, $searchValue, $innerQuery);
            });
        }

        $ids = $query->pluck('id')->all();

        return $this->removeFalseMatches($field, $searchValue, $className, $filterNames, $ids);
    }

    /**
     * Apply the inner part of a whereExists query
     *
     * @param \Stickee\LaravelSearchEncryptedData\Contracts\SearchableInterface $model The model being searched
     * @param string $filterName The filter name
     * @param string $searchValue The string to search for
     * @param \Illuminate\Database\Query\Builder $query The query
     */
    protected function applyQuery(SearchableInterface $model, string $filterName, string $searchValue, Builder $query): void
    {
        $className = get_class($model);
        $hash = $model->getFilter($filterName)->getHash($searchValue);

        $query->select(DB::raw(1))
            ->from('searchables')
            ->where('searchables.searchable_type', $className)
            ->whereColumn('searchables.searchable_id', $model->getQualifiedKeyName())
            ->where('filter_name', $filterName)
            ->where('filter_value', $hash);
    }

    /**
     * Remove false matches
     * Since the search can work on substrings, some filters will return false matches.
     * For example if the filter is "first 3 characters" and the search is "ABCDEF" then "DEF" will not be used in the query,
     * i.e. the search will bring back all records beginning "ABC". This will then remove any that do not start with "ABCDEF"
     *
     * This performs an "and" match, i.e. all filters must match. You may want to override this function if creating a custom executor
     *
     * @param string $field The field to search
     * @param string $searchValue The string to search for
     * @param string $className The Eloquent model class name
     * @param array $filterNames The filters to apply
     * @param array $ids The IDs already matched
     *
     * @return array
     */
    protected function removeFalseMatches(string $field, string $searchValue, string $className, array $filterNames, array $ids): array
    {
        $result = [];

        $className::whereIn('id', $ids)
            ->get(['id', $field])
            ->each(function ($model) use ($filterNames, $field, $searchValue, &$result) {
                foreach ($filterNames as $filterName) {
                    if (!$model->getFilter($filterName)->matches($model->{$field}, $searchValue)) {
                        return;
                    }
                }

                $result[] = $model->id;
            });

        return $result;
    }
}
