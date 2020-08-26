<?php

namespace Stickee\LaravelSearchEncryptedData\Console\Commands;

use Illuminate\Console\Command;
use Stickee\LaravelSearchEncryptedData\Services\LaravelSearchEncryptedDataService;

class Search extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string $signature
     */
    protected $signature = 'search-encrypted-data:search
        {model : The model name}
        {field : The field name}
        {search : The string to search for}';

    /**
     * The console command description
     *
     * @var string $description
     */
    protected $description = 'Search for a given string';

    /**
     * Execute the console command
     *
     * @param \Stickee\LaravelSearchEncryptedData\Services\LaravelSearchEncryptedDataService $service The searcher service
     */
    public function handle(LaravelSearchEncryptedDataService $service): void
    {
        $field = $this->argument('field');
        $search = $this->argument('search');
        $modelName = $this->argument('model');
        $modelName = preg_replace('_^\\\\_', '', $modelName);

        if (!class_exists($modelName)) {
            $this->error('Model ' . $modelName . ' not found');

            return;
        }

        if (empty((new $modelName)->getFiltersForField($field))) {
            $this->error('Not a searchable field: ' . $field);

            return;
        }

        $results = $modelName::withSearchable($field, $search)->get();

        if (!$results->count()) {
            $this->info('No results');
        } else {
            $results = $results->map(function ($model) use ($field) {
                return [
                    $model->id,
                    $model->$field,
                ];
            });

            $this->table(['id', $field], $results);
        }
    }
}
