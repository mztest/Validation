<?php

/*
 * This file is part of Respect\Validation.
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace Respect\Validation\Rules;

use Respect\Validation\Exceptions\KeyException;
use Respect\Validation\Result;

/**
 * Validates if the given input is not empty.
 */
final class Key implements RuleRequiredInterface
{
    private $key;
    private $rule;
    private $mandatory;

    /**
     * @param mixed         $key
     * @param RuleInterface $rule
     * @param bool          $mandatory
     */
    public function __construct($key, RuleInterface $rule, $mandatory)
    {
        $this->key = $key;
        $this->rule = $rule;
        $this->mandatory = (bool) $mandatory;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(Result $result)
    {
        $result->setProperty('key', $this->key);

        $input = $result->getInput();
        if (!isset($input[$this->key])) {
            $result->setProperty('template', KeyException::MESSAGE_KEY);
            $result->setProperty('validation', !$this->mandatory);

            return;
        }

        $childResult = $result->createChild($this->rule);
        $childResult->setProperty('label', $this->key);
        $childResult->setProperty('input', $input[$this->key]);
        $childResult->applyRule();

        $result->setProperty('validation', $childResult->isValid());
    }
}
