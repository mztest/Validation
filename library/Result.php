<?php

/*
 * This file is part of Respect\Validation.
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace Respect\Validation;

use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Rules\RuleInterface;
use Respect\Validation\Rules\RuleRequiredInterface;
use SplObjectStorage;

class Result
{
    use NotOptionalTrait;

    /**
     * @var SplObjectStorage
     */
    private $children;

    /**
     * @var RuleInterface
     */
    private $rule;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var array
     */
    protected $properties = [
        'mode' => ValidationException::MODE_AFFIRMATIVE,
        'validation' => true,
        'input' => null,
    ];

    /**
     * @param RuleInterface $rule
     * @param array         $properties
     * @param Factory       $factory
     */
    public function __construct(RuleInterface $rule, array $properties, Factory $factory)
    {
        $this->rule = $rule;
        $this->properties = $properties + $this->properties;
        $this->factory = $factory;
        $this->children = new SplObjectStorage();
    }

    /**
     * @return RuleInterface
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return Factory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->getProperty('input');
    }

    /**
     * @param string $name
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function getProperty($name, $defaultValue = null)
    {
        if (array_key_exists($name, $this->properties)) {
            $defaultValue = $this->properties[$name];
        }

        return $defaultValue;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;
    }

    /**
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        foreach ($properties as $name => $value) {
            $this->setProperty($name, $value);
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return (bool) $this->getProperty('validation', true);
    }

    /**
     * Apply rule to the result.
     */
    public function applyRule()
    {
        $rule = $this->getRule();

        if (!$rule instanceof RuleRequiredInterface
            && !$this->isNotOptional($this->getInput())) {
            $this->setProperty('validation', true);

            return;
        }

        $rule->apply($this);
    }

    /**
     * @param RuleInterface $rule
     *
     * @return Result
     */
    public function createChild(RuleInterface $rule)
    {
        $childResult = $this->getFactory()->createResult($rule, $this->getProperties());
        $childResult->appendTo($this);

        return $childResult;
    }

    /**
     * @param Result $parentResult
     */
    public function appendTo(Result $parentResult)
    {
        $parentResult->appendChild($this);
    }

    /**
     * @param Result $childChild
     */
    public function appendChild(Result $childChild)
    {
        $this->children->attach($childChild);
    }

    /**
     * @return SplObjectStorage
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return ($this->children->count() > 0);
    }
}
