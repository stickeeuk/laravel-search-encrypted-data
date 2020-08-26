<?php

namespace Stickee\LaravelSearchEncryptedData\Filters;

class Equals extends EqualsCaseSensitive
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
        return mb_strtolower(parent::getFormattedValue($value));
    }
}
