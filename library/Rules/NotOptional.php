<?php

/*
 * This file is part of Respect\Validation.
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace Respect\Validation\Rules;

use Respect\Validation\Result;
use Respect\Validation\NotOptionalTrait;

/**
 * Validates if the given input is not optional.
 */
final class NotOptional implements RuleRequiredInterface
{
    use NotOptionalTrait;

    /**
     * {@inheritDoc}
     */
    public function apply(Result $result)
    {
        $result->setProperty('validation', $this->isNotOptional($result->getInput()));
    }
}
