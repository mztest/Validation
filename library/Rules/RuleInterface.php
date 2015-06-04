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
 * Default interface for rules.
 */
interface RuleInterface
{
    /**
     * Apply rule on the given result.
     *
     * @param Result $result
     */
    public function apply(Result $result);
}
