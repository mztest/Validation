<?php

/*
 * This file is part of Respect\Validation.
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace Respect\Validation\Rules;

use Respect\Validation\Result;

/**
 * Validates if the given input is not empty.
 */
final class NotEmpty implements RuleRequiredInterface
{
    /**
     * {@inheritDoc}
     */
    public function apply(Result $result)
    {
        $result->setProperty('validation', !empty($result->getInput()));
    }
}
