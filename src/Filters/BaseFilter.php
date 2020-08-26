<?php

namespace Stickee\LaravelSearchEncryptedData\Filters;

use Illuminate\Support\Str;
use Stickee\LaravelSearchEncryptedData\Contracts\FilterInterface;

abstract class BaseFilter implements FilterInterface
{
    /**
     * Get a formatted (canonical) version for matching
     *
     * @param string $value The value to format
     *
     * @return string
     */
    abstract protected function getFormattedValue(string $value): string;

    /**
     * Get a hashable value (usually a substring of the formatted value)
     *
     * @param string $value The value to transform
     *
     * @return string
     */
    abstract protected function getHashableValue(string $value): string;

    /**
     * Get a hash of the value (after it has been transformed)
     *
     * @param string $value The value to hash
     *
     * @return string
     */
    public function getHash(string $value): string
    {
        $value = $this->getHashableValue($value);

        if ($value === '') {
            return '';
        }

        return hash_hmac('sha512/256', $value, config('app.key'));
    }

    /**
     * If the filter applies to the search value
     * For example, if the filter is "first 3 characters" and the search value is only two characters
     * long then it would not apply
     *
     * @param string $searchValue The value being searched for
     *
     * return bool
     */
    public function appliesTo(string $searchValue): bool
    {
        return true;
    }
}
