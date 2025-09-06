<?php

declare(strict_types=1);

namespace Give\Vendors\StellarWP\Validation\Contracts;

interface ValidatesOnFrontEnd
{
    /**
     * Serializes the rule option for use on the front-end.
     *
     * @since 1.0.0
     *
     * @return mixed
     */
    public function serializeOption();
}
