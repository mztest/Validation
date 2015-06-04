<?php

/*
 * This file is part of Respect\Validation.
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace Respect\Validation;

use Countable;
use RecursiveIterator;
use SplObjectStorage;

class RecursiveResultIterator implements RecursiveIterator, Countable
{
    /**
     * @var SplObjectStorage
     */
    protected $results;

    /**
     * @param Result $result
     */
    public function __construct(Result $result)
    {
        $this->results = $result->getChildren();
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->results->count();
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        if (!$this->valid()) {
            return false;
        }

        return $this->current()->hasChildren();
    }

    /**
     * @return RecursiveResultIterator
     */
    public function getChildren()
    {
        return new static($this->current());
    }

    /**
     * @return Result
     */
    public function current()
    {
        return $this->results->current();
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->results->key();
    }

    public function next()
    {
        $this->results->next();
    }

    public function rewind()
    {
        $this->results->rewind();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->results->valid();
    }
}
