<?php

namespace Stickee\LaravelSearchEncryptedData\Filters;

class StartsWith extends StartsWithCaseSensitive
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
