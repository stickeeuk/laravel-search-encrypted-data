<?php

namespace Stickee\LaravelSearchEncryptedData\Test\Unit;

use Illuminate\Support\Facades\DB;
use Stickee\LaravelSearchEncryptedData\Models\Searchable;
use Stickee\LaravelSearchEncryptedData\Test\TestCase;
use Stickee\LaravelSearchEncryptedData\Test\TestModelWithExecutor;

class CustomExecutorTest extends TestCase
{
    /**
     * Test searching with a custom executor
     */
    public function test_searching()
    {
        DB::transaction(function () {
            TestModelWithExecutor::create([
                'id' => 1,
                'first_name' => 'Tester',
                'email' => 'tester@example.com',
            ]);

            TestModelWithExecutor::create([
                'id' => 2,
                'first_name' => 'Testerson',
                'email' => 'testerson@example.com',
            ]);

            TestModelWithExecutor::create([
                'id' => 3,
                'first_name' => 'Testington',
                'email' => 'testington@example.com',
            ]);

            TestModelWithExecutor::create([
                'id' => 4,
                'first_name' => 'test',
                'email' => 'test@example.com',
            ]);

            TestModelWithExecutor::create([
                'id' => 5,
                'first_name' => 'Spitfire',
                'email' => 'spitfire@example.com',
            ]);

            TestModelWithExecutor::create([
                'id' => 6,
                'first_name' => 'Soarin',
                'email' => 'soarin@example.com',
            ]);

            // Expect no results because it doesn't meet the minimum length
            $this->assertEqualsCanonicalizing(
                [],
                TestModelWithExecutor::withSearchable('first_name', 't')->pluck('id')->all(),
                'Invalid result when no filters match'
            );

            $this->assertEqualsCanonicalizing(
                [1, 2, 3, 4],
                TestModelWithExecutor::withSearchable('first_name', 'test')->pluck('id')->all(),
                'Invalid result when one filter matches'
            );

            $this->assertEqualsCanonicalizing(
                [5],
                TestModelWithExecutor::withSearchable('first_name', 'ire')->pluck('id')->all(),
                'Invalid result when two filters match'
            );
        });
    }
}
