<?php

namespace Stickee\LaravelSearchEncryptedData\Filters;

class EqualsCaseSensitive extends BaseFilter
{
    /**
     * Get a formatted (canonical) version for matching
     *
     * @param string $value The value to format
     *
     * @return string
     */
    protected function getFormattedValue(string $value): string
    {
        return $value;
    }

    /**
     * Get a hashable value (usually a substring of the formatted value)
     *
     * @param string $value The value to transform
     *
     * @return string
     */
    protected function getHashableValue(string $value): string
    {
        return $this->getFormattedValue($value);
    }

    /**
     * Test if a search value matches the actual value
     *
     * @param string $value The model's real value
     * @param string $searchValue The value being searched for
     *
     * @return bool
     */
    public function matches(string $value, string $searchValue): bool
    {
        return $this->getFormattedValue($value) === $this->getFormattedValue($searchValue);
    }
}
