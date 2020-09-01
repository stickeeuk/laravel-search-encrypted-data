# Stickee Laravel Search Encrypted Data

This a composer module for searching encrypted data.

## Contents
 - [Quick Start](#quick-start)
 - [Installation](#installation)
   - [Manual Registration](#manual-registration)
 - [Configuration](#configuration)
 - [Usage](#usage)
   - [Adding filters to a model](#adding-filters-to-a-model)
   - [Built in filters](#built-in-filters)
   - [Custom filters](#custom-filters)
   - [Custom search executor](#custom-search-executor)
   - [Searching computed data](#searching-computed-data)
 - [Commands](#customisation)
   - [Update searchable](#update-searchable)
   - [Search](#search)
 - [Developing](#developing)

## Quick Start

1. `composer require stickee/laravel-search-encrypted-data`
2. `php artisan migrate`
3. Add the `\Stickee\LaravelSearchEncryptedData\Contracts\SearchableInterface` interface
  and `\Stickee\LaravelSearchEncryptedData\Searchable` trait to your model(s)
  ```
  use Stickee\LaravelSearchEncryptedData\Contracts\SearchableInterface;
  use Stickee\LaravelSearchEncryptedData\Searchable;

  class MyModel extends Model implements SearchableInterface {
    use Searchable;
  }
  ```
4. Add the filters to your class, for example
  ```
  use Stickee\LaravelSearchEncryptedData\Contracts\SearchableInterface;
  use Stickee\LaravelSearchEncryptedData\Filters\Equals;
  use Stickee\LaravelSearchEncryptedData\Filters\StartsWith;
  use Stickee\LaravelSearchEncryptedData\Searchable;

  class MyModel extends Model implements SearchableInterface {
    use Searchable;

    public $searchable = [
      [StartsWith::class, 'first_name', 4],
      [Equals::class, 'email'],
    ];
  }
  ```
5. Run `php artisan search-encrypted-data:update-searchable <class>` to update existing instances, e.g.
  `php artisan search-encrypted-data:update-searchable "App\Models\MyModel"`
6. Use the `withSearchable` to search, for example
  ```
  $results = MyModel::withSearchable('first_name', 'test')->where('active', true)->get();
  ```

## Installation

`composer require stickee/laravel-search-encrypted-data`

This module ships with a Laravel service provider which will be automatically registered for Laravel 5.5+.

### Manual registration

The module can be manually registered by adding this to the `providers` array in `config/app.php`:

```
Stickee\LaravelSearchEncryptedData\ServiceProvider::class,
```

## Configuration

Publish the configuration file to your project with this command:

`php artisan vendor:publish --provider=Stickee\\LaravelSearchEncryptedData\\ServiceProvider --tag=config`

| Name | Type | Default | Description |
|------|------|---------|-------------|
bulk_insert_amount | `int` | `1000` | The number of rows to insert at a time for bulk inserts |
bulk_delete_amount | `int` | `1000` | The number of rows to insert at a time for bulk deletions |
default_filters_executor | `string` | `\Stickee\LaravelSearchEncryptedData\FiltersExecutor::class` | The default filter executor class name |

## Usage

The search works via this process:

1. A model defines one or more "filters" for each encrypted field that needs to be searched
2. When a model is created or updated, each filter takes the value of the field it is associated with,
  canonicalises it and hashes the result (using app.key - if you change the key you will need to recreate all hashes using the supplied command)
3. When a search is performed using the `withSearchable` scope, the search string undergoes the same canonicalisation and hashing process for each filter, and the hash is searched for in the database
4. All matching models are retrieved (in whole or in part) to remove false-positives
5. The IDs are added to a `whereIn` on the query

An example search would look like
```
$results = MyModel::withSearchable('first_name', 'test')->where('active', true)->get();
```

The first argument is the field name, the second is the search string.

### Adding filters to a model

Filters are added as an array called "searchable" to the model, e.g.
```
public $searchable = [
  [StartsWith::class, 'first_name', 4],
  [StartsWith::class, 'last_name'],
  [Equals::class, 'email'],
  [Custom::class, 'some_field', 'something', true, 123],
];
```

The array can be numeric or associative - the key is called the `filterName` internally.
If you are writing a custom search executor then using an associative array is recommended, else use a numeric array.
Each element of the array should be an array containing:

1. The class name of the filter
2. The field (i.e. model property) that the filter applies to
3. Extra constructor parameters the filter requires (if any)

### Built-in filters

The following filters are supplied in the `Stickee\LaravelSearchEncryptedData\Filters` namespace:

 - `EndsWith($length = 3)`
 - `EndsWithCaseSensitive($length = 3)`
 - `Equals`
 - `StartsWith($length = 3)`
 - `StartsWithCaseSensitive($length = 3)`

### Custom filters

Custom filters must implement the `\Stickee\LaravelSearchEncryptedData\Contracts\FilterInterface` interface.

### Custom search executor

Using the default search executor, all filters on a field must pass for the instance to be considered a match. If you need different logic, e.g. "the first 3 letters or the last 3 letters", you should implement a custom search executor.

A custom executor must implement the `\Stickee\LaravelSearchEncryptedData\ContractsFiltersExecutorInterface` interface.

The general process for an executor will be:

1. Generate a list of model IDs that match the filters
2. Remove false-positives and return the list

False positives may occur because some filters work on limited substrings, e.g. `StartsWith`. If the `length` is set to 3, and you pass in "ABCDEF" as the search, then all records starting with "ABC" will be matched. Removing those that don't start "ABCDEF" must be done as a second step.

An Eloquent model can be made to use your custom executor by setting the `searchableFiltersExecutor` property, for example:
```
class MyModel extends Model implements SearchableInterface {
  use Searchable;

  public $searchableFiltersExecutor = \App\MyCustomExecutor::class;
}
```

### Searching computed data

It is possible to search computed data by:

1. If you don't already have one, define a Laravel attribute accessor method, e.g
```
public function getFullNameAttribute(): string
{
  return $this->first_name . ' ' . $this->last_name;
}
```
2. If necessary (it usually will be), override the static `searchableGetColumns` method to return any database columns needed to calculate the attribute, e.g.
```
public static function searchableGetColumns(string $field): array
{
  return $field === 'full_name' ? ['first_name', 'last_name'] : [$field];
}
```

## Commands

### Update searchable

Updates the hashes for a model (optionally for a single filter on that model)

`php artisan search-encrypted-data:update-searchable "App\Models\MyModel"`

### Search

Search for models

`php artisan search-encrypted-data:search "App\Models\MyModel" first_name tester`

## Developing

The easiest way to make changes is to make the project you're importing the module in to load the module from your filesystem instead of the composer repository, like this:

1. `composer remove stickee/laravel-search-encrypted-data`
2. Edit `composer.json` and add
    ```
    "repositories": [
      {
        "type": "path",
        "url": "../laravel-search-encrypted-data"
      }
    ]
    ```
    where "../laravel-search-encrypted-data" is the path to where you have this project checked out
3. `composer require stickee/laravel-search-encrypted-data`

**NOTE:** Do not check in your `composer.json` like this!
