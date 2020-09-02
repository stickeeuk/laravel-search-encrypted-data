<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Bulk insert amount
     |--------------------------------------------------------------------------
     |
     | The number of rows to insert at a time for bulk inserts
     |
     */
    'bulk_insert_amount' => 1000,

    /*
     |--------------------------------------------------------------------------
     | Bulk delete amount
     |--------------------------------------------------------------------------
     |
     | The number of rows to delete at a time for bulk deletions
     |
     */
    'bulk_delete_amount' => 1000,

    /*
     |--------------------------------------------------------------------------
     | Default filters executor
     |--------------------------------------------------------------------------
     |
     | The default filter executor class name - must implement
     | \Stickee\LaravelSearchEncryptedData\Contracts\FiltersExecutorInterface
     |
     */
    'default_filters_executor' => \Stickee\LaravelSearchEncryptedData\FiltersExecutor::class,
];
