<?php

/*
 * This file is part of Respect\Validation.
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace Respect\Validation;

use RecursiveIteratorIterator;
use ReflectionClass;
use Respect\Validation\Exceptions\ComponentException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Rules\RuleInterface;
use SplObjectStorage;

class Factory
{
    /**
     * @var array
     */
    protected $namespaces = ['Respect\\Validation'];

    /**
     * @var array
     */
    protected $resultProperties = [];

    /**
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * @param string $namespace
     */
    public function appendNamespace($namespace)
    {
        array_push($this->namespaces, $namespace);
    }

    /**
     * @param string $namespace
     */
    public function prependNamespace($namespace)
    {
        array_unshift($this->namespaces, $namespace);
    }

    /**
     * @param array $resultProperties
     */
    public function setDefaultResultProperties(array $resultProperties)
    {
        $this->resultProperties = $resultProperties;
    }

    /**
     * @param string $ruleName
     * @param array  $settings
     *
     * @throws ComponentException
     *
     * @return RuleInterface
     */
    public function createRule($ruleName, array $settings = [])
    {
        foreach ($this->getNamespaces() as $namespace) {
            $ruleClassName = $namespace.'\\Rules\\'.ucfirst($ruleName);
            if (!class_exists($ruleClassName)) {
                continue;
            }

            $reflection = new ReflectionClass($ruleClassName);
            if (!$reflection->isSubclassOf('Respect\\Validation\\Rules\\RuleInterface')) {
                throw new ComponentException(sprintf('"%s" is not a valid respect rule', $ruleClassName));
            }

            return $reflection->newInstanceArgs($settings);
        }

        throw new ComponentException(sprintf('"%s" is not a valid rule name', $ruleName));
    }

    /**
     * @param Result $result
     *
     * @throws ComponentException
     *
     * @return ValidationException
     */
    public function createException(Result $result)
    {
        $ruleName = get_class($result->getRule());
        $ruleShortName = substr(strrchr($ruleName, '\\'), 1);
        foreach ($this->getNamespaces() as $namespace) {
            $exceptionClassName = $namespace.'\\Exceptions\\'.$ruleShortName.'Exception';
            if (!class_exists($exceptionClassName)) {
                continue;
            }

            $reflection = new ReflectionClass($exceptionClassName);
            if (!$reflection->isSubclassOf('Respect\\Validation\\Exceptions\\ValidationException')) {
                throw new ComponentException(sprintf('"%s" is not a validation exception', $exceptionClassName));
            }

            return $reflection->newInstance($result);
        }

        throw new ValidationException($result);
    }

    /**
     * @param Result $result
     *
     * @throws ComponentException
     *
     * @return ValidationException
     */
    public function createFilteredException(Result $result)
    {
        $resultIterator = new RecursiveResultIterator($result);
        $iteratorIterator = new RecursiveIteratorIterator($resultIterator);
        foreach ($iteratorIterator as $childResult) {
            $result = $childResult;
            break;
        }

        return $this->createException($result);
    }

    /**
     * @return SplObjectStorage
     */
    public function createChildrenExceptions(Result $result)
    {
        $childrenExceptions = new SplObjectStorage();

        $resultIterator = new RecursiveResultIterator($result);
        $iteratorIterator = new RecursiveIteratorIterator($resultIterator, RecursiveIteratorIterator::SELF_FIRST);

        $lastDepth = 0;
        $lastDepthOriginal = 0;
        $knownDepths = [];
        foreach ($iteratorIterator as $childResult) {
            if ($childResult->isValid()) {
                continue;
            }

            if ($childResult->hasChildren()
                && $childResult->getChildren()->count() < 2) {
                continue;
            }

            $currentDepth = $lastDepth;
            $currentDepthOriginal = $iteratorIterator->getDepth() + 1;

            if (isset($knownDepths[$currentDepthOriginal])) {
                $currentDepth = $knownDepths[$currentDepthOriginal];
            } elseif ($currentDepthOriginal > $lastDepthOriginal) {
                $currentDepth++;
            }

            if (!isset($knownDepths[$currentDepthOriginal])) {
                $knownDepths[$currentDepthOriginal] = $currentDepth;
            }

            $lastDepth = $currentDepth;
            $lastDepthOriginal = $currentDepthOriginal;

            $childrenExceptions->attach(
                $this->createException($childResult),
                [
                    'depth' => $currentDepth,
                    'depth_original' => $currentDepthOriginal,
                    'previous_depth' => $lastDepth,
                    'previous_depth_original' => $lastDepthOriginal,
                ]
            );
        }

        return $childrenExceptions;
    }

    /**
     * @return Result
     */
    public function createResult(RuleInterface $rule, array $properties)
    {
        $resultProperties = $properties + $this->resultProperties;

        return new Result($rule, $resultProperties, $this);
    }
}
