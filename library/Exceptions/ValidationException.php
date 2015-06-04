<?php

/*
 * This file is part of Respect\Validation.
 *
 * For the full copyright and license information, please view the "LICENSE.md"
 * file that was distributed with this source code.
 */

namespace Respect\Validation\Exceptions;

use InvalidArgumentException;
use IteratorAggregate;
use Respect\Validation\Result;
use SplObjectStorage;

class ValidationException extends InvalidArgumentException implements ExceptionInterface, IteratorAggregate
{
    const MESSAGE_STANDARD = 0;

    const MODE_AFFIRMATIVE = 1;
    const MODE_NEGATIVE = 0;

    /**
     * @var Result
     */
    private $result;

    /**
     * @var SplObjectStorage
     */
    private $children;

    /**
     * @param Result $result
     */
    public function __construct(Result $result)
    {
        $this->result = $result;
        $this->message = $this->createMessage();
    }

    /**
     * @return Result
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Returns all available templates.
     *
     * You must overwrite this method for custom message.
     *
     * @return array
     */
    public function getTemplates()
    {
        return [
            self::MODE_AFFIRMATIVE => [
                self::MESSAGE_STANDARD => '{{placeholder}} must be valid',
            ],
            self::MODE_NEGATIVE => [
                self::MESSAGE_STANDARD => '{{placeholder}} must not be valid',
            ],
        ];
    }

    /**
     * Returns the current mode.
     *
     * @return int
     */
    public function getKeyMode()
    {
        return $this->getResult()->getProperty('mode');
    }

    /**
     * Returns the current mode.
     *
     * @return int
     */
    public function getKeyTemplate()
    {
        return $this->getResult()->getProperty('template') ?: self::MESSAGE_STANDARD;
    }

    /**
     * @return string
     */
    private function getMessageTemplate()
    {
        if ($this->getResult()->getProperty('message')) {
            return $this->getResult()->getProperty('message');
        }

        $keyMode = $this->getKeyMode();
        $keyTemplate = $this->getKeyTemplate();
        $templates = $this->getTemplates();

        if (isset($templates[$keyMode][$keyTemplate])) {
            return $templates[$keyMode][$keyTemplate];
        }

        return current(current($templates));
    }

    /**
     * @return string
     */
    private function getPlaceholder()
    {
        $result = $this->getResult();
        $placeholder = $result->getProperty('input');

        if (is_scalar($placeholder)) {
            $placeholder = var_export($placeholder, true);
        }

        if (!empty($result->getProperty('label'))) {
            $placeholder = $result->getProperty('label');
        }

        if (is_array($placeholder)) {
            $placeholder = '`Array`';
        }

        if (is_object($placeholder)) {
            $placeholder = sprintf('`%s`', get_class($placeholder));
        }

        return $placeholder;
    }

    /**
     * @return string
     */
    private function createMessage()
    {
        $params = $this->getResult()->getProperties();
        $params['placeholder'] = $this->getPlaceholder();
        $template = $this->getMessageTemplate();

        if (isset($params['message_filter_callback'])) {
            $template = call_user_func($params['message_filter_callback'], $template);
        }

        return preg_replace_callback(
            '/{{(\w+)}}/',
            function ($match) use ($params) {
                $value = $match[0];
                if (isset($params[$match[1]])) {
                    $value = $params[$match[1]];
                }

                return $value;
            },
            $template
        );
    }

    /**
     * @return Factory
     */
    private function getFactory()
    {
        return $this->getResult()->getFactory();
    }

    /**
     * @return SplObjectStorage
     */
    public function getIterator()
    {
        if (!$this->children instanceof SplObjectStorage) {
            $this->children = $this->getFactory()->createChildrenExceptions($this->getResult());
        }

        return $this->children;
    }

    /**
     * @return string
     */
    public function getFullMessage()
    {
        $marker = '-';
        $exceptions = $this->getIterator();
        $messages = [sprintf('%s %s', $marker, $this->getMessage())];
        foreach ($exceptions as $exception) {
            $depth = $exceptions[$exception]['depth'];
            $prefix = str_repeat(' ', $depth * 2);
            $messages[] .= sprintf('%s%s %s', $prefix, $marker, $exception->getMessage());
        }

        return implode(PHP_EOL, $messages);
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        $messages = [$this->getMessage()];
        foreach ($this->getIterator() as $exception) {
            $messages[] = $exception->getMessage();
        }

        return $messages;
    }
}
