<?php

namespace Stickee\LaravelSearchEncryptedData\Filters;

use Illuminate\Support\Str;

class EndsWithCaseSensitive extends BaseFilter
{
    /**
     * The number of characters to include
     *
     * @var int $length
     */
    protected $length;

    /**
     * Constructor
     *
     * @param int $length The number of characters to include
     */
    public function  __construct(int $length = 3)
    {
        $this->length = $length;
    }

    /**
     * Get a formatted (canonical) version for matching
     *
     * @param string $value The value to format
     *
     * @return string
     */
    protected function getFormattedValue(string $value): string
    {
        $value = preg_replace('/\s/', '', $value);

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
        $value = $this->getFormattedValue($value);
        $value = mb_substr($value, -$this->length);

        return $value;
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
        return Str::endsWith(
            $this->getFormattedValue($value),
            $this->getFormattedValue($searchValue)
        );
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
        return mb_strlen($this->getFormattedValue($searchValue)) >= $this->length;
    }
}
