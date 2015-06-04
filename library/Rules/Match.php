<?php

/*
 * This file is part of Respect\Validation.
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace Respect\Validation\Rules;

use Respect\Validation\Result;

final class Match implements RuleInterface
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @param string $pattern
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(Result $result)
    {
        $result->setProperties([
            'pattern' => $this->pattern,
            'validation' => is_scalar($result->getInput()) && preg_match($this->pattern, $result->getInput()),
        ]);
    }
}
