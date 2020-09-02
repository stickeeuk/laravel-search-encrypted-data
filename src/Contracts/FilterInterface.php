<?php

namespace Stickee\LaravelSearchEncryptedData\Contracts;

interface FilterInterface
{
    /**
     * Get a hash of the value (after it has been transformed)
     *
     * @param string $value The value to hash
     *
     * @return string
     */
    public function getHash(string $value): string;

    /**
     * Test if a search value matches the actual value
     *
     * @param string $value The model's real value
     * @param string $searchValue The value being searched for
     *
     * @return bool
     */
    public function matches(string $value, string $searchValue): bool;

    /**
     * If the filter applies to the search value
     * For example, if the filter is "first 3 characters" and the search value is only two characters
     * long then it would not apply
     *
     * @param string $searchValue The value being searched for
     *
     * return bool
     */
    public function appliesTo(string $searchValue): bool;
}
