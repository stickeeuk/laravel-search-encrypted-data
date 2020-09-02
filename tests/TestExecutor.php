<?php

namespace Stickee\LaravelSearchEncryptedData\Test;

use Stickee\LaravelSearchEncryptedData\Contracts\SearchableInterface;
use Stickee\LaravelSearchEncryptedData\FiltersExecutor;

/**
 */
class TestExecutor extends FiltersExecutor
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
        $usedFilterNames = [];

        if (empty($filterNames)) {
            return [];
        }

        $query = $className::query();

        if (in_array('first_name_starts_with_6', $filterNames)) {
            $usedFilterNames[] = 'first_name_starts_with_6';

            $query->orWhereExists(function ($innerQuery) use ($model, $searchValue) {
                $this->applyQuery($model, 'first_name_starts_with_6', $searchValue, $innerQuery);
            });
        } elseif (in_array('first_name_starts_with_3', $filterNames)) {
            $usedFilterNames[] = 'first_name_starts_with_3';

            $query->orWhereExists(function ($innerQuery) use ($model, $searchValue) {
                $this->applyQuery($model, 'first_name_starts_with_3', $searchValue, $innerQuery);
            });
        }

        if (in_array('first_name_ends_with', $filterNames)) {
            $usedFilterNames[] = 'first_name_ends_with';

            $query->orWhereExists(function ($innerQuery) use ($model, $searchValue) {
                $this->applyQuery($model, 'first_name_ends_with', $searchValue, $innerQuery);
            });
        }

        $ids = $query->pluck('id')->all();

        return $this->removeFalseMatches($field, $searchValue, $className, $usedFilterNames, $ids);
    }

    /**
     * Remove false matches
     * Since the search can work on substrings, some filters will return false matches.
     * For example if the filter is "first 3 characters" and the search is "ABCDEF" then "DEF" will not be used in the query,
     * i.e. the search will bring back all records beginning "ABC". This will then remove any that do not start with "ABCDEF"
     *
     * This performs an "or" match, i.e. at least one filter must match
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
                    if ($model->getFilter($filterName)->matches($model->{$field}, $searchValue)) {
                        $result[] = $model->id;

                        return;
                    }
                }
            });

        return $result;
    }
}
