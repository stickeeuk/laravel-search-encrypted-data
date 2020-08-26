<?php

namespace Stickee\LaravelSearchEncryptedData\Console\Commands;

use Illuminate\Console\Command;
use Stickee\LaravelSearchEncryptedData\Services\LaravelSearchEncryptedDataService;

class UpdateSearchable extends Command
{
    /**
     * The name and signature of the console command
     *
     * @var string $signature
     */
    protected $signature = 'search-encrypted-data:update-searchable
        {model : The model name}
        {filter? : The filter name (or index)}
        {--force : Don\'t prompt}';

    /**
     * The console command description
     *
     * @var string $description
     */
    protected $description = 'Update an encrypted search filter if the filter changes or a new filter is created';

    /**
     * Execute the console command
     *
     * @param \Stickee\LaravelSearchEncryptedData\Services\LaravelSearchEncryptedDataService $service The searcher service
     */
    public function handle(LaravelSearchEncryptedDataService $service): void
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will put the site into maintenance mode - continue?')) {
                return;
            }
        }

        $filterName = $this->argument('filter');
        $modelName = $this->argument('model');
        $modelName = preg_replace('_^\\\\_', '', $modelName);

        if (!class_exists($modelName)) {
            $this->error('Model ' . $modelName . ' not found');

            return;
        }

        // Set maintenance mode to avoid problems with unique constraints
        $this->call('down');

        if ($filterName) {
            $service->updateModelFilter($modelName, $filterName);
        } else {
            $service->updateModel($modelName);
        }

        // Remove maintenance mode
        $this->call('up');
    }
}
