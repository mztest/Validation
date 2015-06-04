<?php

/*
 * This file is part of Respect\Validation.
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace Respect\Validation\Rules;

use RecursiveIteratorIterator;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\RecursiveResultIterator;
use Respect\Validation\Result;

/**
 * Negates any rule.
 */
final class Not implements RuleRequiredInterface
{
    /**
     * @var RuleInterface
     */
    protected $rule;

    /**
     * @param RuleInterface $rule
     */
    public function __construct(RuleInterface $rule)
    {
        $this->rule = $rule;
    }

    /**
     * @return RuleInterface
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(Result $result)
    {
        $childResult = $result->createChild($this->getRule());
        $childResult->applyRule();

        $resultIterator = new RecursiveResultIterator($result);
        $iteratorIterator = new RecursiveIteratorIterator($resultIterator, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iteratorIterator as $grandChildResult) {
            if (!$grandChildResult->hasChildren()) {
                $currentMode = $grandChildResult->getProperty('mode', ValidationException::MODE_AFFIRMATIVE);
                $updatedMode = $currentMode == ValidationException::MODE_AFFIRMATIVE
                            ? ValidationException::MODE_NEGATIVE
                            : ValidationException::MODE_AFFIRMATIVE;
                $grandChildResult->setProperty('mode', $updatedMode);
            }

            $grandChildResult->setProperty('validation', !$grandChildResult->isValid());
        }

        $result->setProperty('validation', $childResult->isValid());
    }
}
