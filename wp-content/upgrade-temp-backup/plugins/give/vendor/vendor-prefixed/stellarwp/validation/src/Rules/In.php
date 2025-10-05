<?php

namespace Give\Vendors\StellarWP\Validation\Rules;

use Closure;
use Give\Vendors\StellarWP\Validation\Config;
use Give\Vendors\StellarWP\Validation\Contracts\ValidatesOnFrontEnd;
use Give\Vendors\StellarWP\Validation\Contracts\ValidationRule;

class In implements ValidationRule, ValidatesOnFrontEnd
{
    /**
     * @var array<mixed>
     */
    protected array $acceptedValues;

    /**
     * @since 1.2.0
     */
    public static function id(): string
    {
        return 'in';
    }

    /**
     * @since 1.2.0
     *
     * @param mixed ...$acceptedValues
     */
    final public function __construct(...$acceptedValues)
    {
        if (empty($acceptedValues)) {
            Config::throwInvalidArgumentException('The In rule requires at least one value to be specified.');
        }

        $this->acceptedValues = $acceptedValues;
    }

    /**
     * @since 1.2.0
     */
    public static function fromString(?string $options = null): ValidationRule
    {
        if (empty(trim($options))) {
            Config::throwInvalidArgumentException('The In rule requires at least one value to be specified.');
        }

        $values = explode(',', $options);

        if (empty($values)) {
            Config::throwInvalidArgumentException('The In rule requires at least one value to be specified.');
        }

        return new static(...$values);
    }

    /**
     * @since 1.2.0
     */
    public function __invoke($value, Closure $fail, string $key, array $values)
    {
        if (!in_array($value, $this->acceptedValues)) {
            $fail(sprintf(__('%s must be one of %s', 'give'), '{field}', implode(', ', $this->acceptedValues)));
        }
    }

    /**
     * @since 1.2.0
     *
     * @return array<mixed>
     */
    public function serializeOption(): array
    {
        return $this->acceptedValues;
    }
}
