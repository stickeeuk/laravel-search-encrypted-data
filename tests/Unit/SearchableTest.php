<?php

namespace Stickee\LaravelSearchEncryptedData\Test\Unit;

use Illuminate\Support\Facades\DB;
use Stickee\LaravelSearchEncryptedData\Models\Searchable;
use Stickee\LaravelSearchEncryptedData\Test\TestCase;
use Stickee\LaravelSearchEncryptedData\Test\TestModel;
use Stickee\LaravelSearchEncryptedData\Test\TestModelCaseSensitive;

class SearchableTest extends TestCase
{
    /**
     * Test searching
     */
    public function test_searching()
    {
        DB::transaction(function () {
            TestModel::create([
                'id' => 1,
                'first_name' => 'Tester',
                'email' => 'tester@example.com',
            ]);

            TestModel::create([
                'id' => 2,
                'first_name' => 'Testerson',
                'email' => 'testerson@example.com',
            ]);

            TestModel::create([
                'id' => 3,
                'first_name' => 'Testington',
                'email' => 'testington@example.com',
            ]);

            TestModel::create([
                'id' => 4,
                'first_name' => 'test',
                'email' => 'test@example.com',
            ]);

            TestModelCaseSensitive::create([
                'id' => 5,
                'first_name' => 'TESTINGTON',
                'email' => 'testington_upper@example.com',
            ]);

            TestModelCaseSensitive::create([
                'id' => 6,
                'first_name' => 'TESTINGTON THE SECOND',
                'email' => 'testington_the_second_upper@example.com',
            ]);

            // Expect no results because it doesn't meet the minimum length
            $this->assertEqualsCanonicalizing(
                [],
                TestModel::withSearchable('first_name', 't')->pluck('id')->all(),
                'Invalid result when no filters match'
            );

            $this->assertEqualsCanonicalizing(
                [1, 2, 3, 4],
                TestModel::withSearchable('first_name', 'test')->pluck('id')->all(),
                'Invalid result when one filter matches'
            );

            $this->assertEqualsCanonicalizing(
                [1, 2],
                TestModel::withSearchable('first_name', 'tester')->pluck('id')->all(),
                'Invalid result when two filters match'
            );

            $this->assertEqualsCanonicalizing(
                [2],
                TestModel::withSearchable('first_name', 'testerson')->pluck('id')->all(),
                'Invalid result when two filters match and the search is longer than the minimum'
            );

            $this->assertEqualsCanonicalizing(
                [],
                TestModelCaseSensitive::withSearchable('first_name', 'testington')->pluck('id')->all(),
                'Invalid result when no filters match'
            );

            $this->assertEqualsCanonicalizing(
                [5, 6],
                TestModelCaseSensitive::withSearchable('first_name', 'TESTING')->pluck('id')->all(),
                'Invalid result when two filters match and the search is longer than the minimum'
            );
        });
    }

    /**
     * Test we can override $searchable in a subclass
     */
    public function test_subclassing()
    {
        DB::transaction(function () {
            $model = TestModel::create([
                'first_name' => 'Tester',
                'email' => 'tester@example.com',
            ]);

            $model2 = TestModelCaseSensitive::create([
                'first_name' => 'TESTINGTON',
                'email' => 'testington_upper@example.com',
            ]);

            $this->assertSame(
                'db873ee5689ad3e3ceff8d16116c16ccb4e1353fd15e8d35ae57b580826303d3',
                $model->getFilterHash('first_name_starts_with_3'),
                'Invalid hash for first name'
            );

            $this->assertSame(
                'd409b6361e6b766a42a43caf82a213bae890e3941eaf25d9be420cd6a79a75b8',
                $model2->getFilterHash('first_name_starts_with_3'),
                'Invalid hash for first name case sensitive'
            );
        });
    }
}