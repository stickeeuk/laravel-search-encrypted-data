<?php

namespace Stickee\LaravelSearchEncryptedData\Contracts;

use Stickee\LaravelSearchEncryptedData\Contracts\SearchableInterface;

interface FiltersExecutorInterface
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
    public function execute(SearchableInterface $model, string $field, string $searchValue): array;
}
