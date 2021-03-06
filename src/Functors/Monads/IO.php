<?php

/**
 * IO monad.
 *
 * @author Lochemem Bruno Michael
 * @license Apache 2.0
 */

namespace Chemem\Bingo\Functional\Functors\Monads;

use function Chemem\Bingo\Functional\Algorithms\constantFunction;

class IO implements Monadic
{
    const of = 'Chemem\\Bingo\\Functional\\Functors\\Monads\\IO::of';

    /**
     * @var callable The unsafe operation to perform
     */
    private $operation;

    /**
     * IO monad constructor.
     *
     * @param callable $operation
     */
    public function __construct(callable $operation)
    {
        $this->operation = $operation;
    }

    /**
     * of method.
     *
     * @static of
     *
     * @param callable $operation
     *
     * @return object IO
     */
    public static function of($operation): self
    {
        return new static(\is_callable($operation) ? $operation : constantFunction($operation));
    }

    /**
     * ap method.
     *
     * @param object IO
     *
     * @return object IO
     */
    public function ap(Monadic $app): Monadic
    {
        return $app->map($this->exec());
    }

    /**
     * map method.
     *
     * @param callable $function
     *
     * @return object IO
     */
    public function map(callable $function): Monadic
    {
        return $this->bind(function ($operation) use ($function) {
            return self::of($function($operation));
        });
    }

    /**
     * bind method.
     *
     * @param callable $function
     *
     * @return object IO
     */
    public function bind(callable $function): Monadic
    {
        return $function($this->exec());
    }

    /**
     * exec method.
     *
     * @return $operation
     */
    public function exec()
    {
        return \call_user_func($this->operation);
    }

    /**
     * flatMap method.
     *
     * @param callable $function
     *
     * @return mixed $operation
     */
    public function flatMap(callable $function)
    {
        return \call_user_func($function, $this->exec());
    }
}
