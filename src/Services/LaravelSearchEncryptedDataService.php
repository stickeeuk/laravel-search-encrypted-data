<?php

namespace Stickee\LaravelSearchEncryptedData\Services;

use Stickee\LaravelSearchEncryptedData\Models\Searchable;

class LaravelSearchEncryptedDataService
{
    /**
     * Update all filters on all instances of a model
     *
     * @param string $className The class of model to update
     */
    public function updateModel(string $className): void
    {
        $instance = app($className);

        foreach ($instance->searchable as $filterName => $filterData) {
            $this->updateModelFilter($className, $filterName);
        }
    }

    /**
     * Update a single filter on all instances of a model
     *
     * @param string $className The class of model to update
     * @param string $filterName The filter name
     */
    public function updateModelFilter(string $className, string $filterName): void
    {
        // Delete in chunks
        do {
            $deleted = Searchable::where('searchable_type', $className)
                ->where('filter_name', $filterName)
                ->limit(config('laravel-search-encrypted-data.bulk_delete_amount'))
                ->delete();
        } while ($deleted > 0);

        // Insert new values in chunks
        $className::chunk(config('laravel-search-encrypted-data.bulk_insert_amount'), function($models) use ($className, $filterName) {
            $data = [];

            foreach ($models as $model) {
                $data[] = [
                    'searchable_type' => $className,
                    'searchable_id' => $model->id,
                    'filter_name' => $filterName,
                    'filter_value' => $model->getFilterHash($filterName),
                ];
            }

            if (!empty($data)) {
                Searchable::insert($data);
            }
        });
    }
}
