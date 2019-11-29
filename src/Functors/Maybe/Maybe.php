<?php

/**
 * Maybe type abstract functor.
 *
 * @author Lochemem Bruno Michael
 * @license Apache 2.0
 */

namespace Chemem\Bingo\Functional\Functors\Maybe;

use \Chemem\Bingo\Functional\Algorithms as f;
use \Chemem\Bingo\Functional\Functors\Monads as M;

abstract class Maybe implements M\Monadic
{
    /**
     * @var string just
     */
    const just      = __CLASS__ . '::just';

    /**
     * @var string nothing
     */
    const nothing   = __CLASS__ . '::nothing';

    /**
     * @var string fromValue
     */
    const fromValue = __CLASS__ . '::fromValue';

    /**
     * @var string lift
     */
    const lift      = __CLASS__ . '::lift';
    
    /**
     * just method.
     *
     * @param mixed $value
     *
     * @return object Just
     */
    public static function just($value): Just
    {
        return new Just($value);
    }

    /**
     * nothing method.
     *
     * @return object Nothing
     */
    public static function nothing(): Nothing
    {
        return new Nothing();
    }

    /**
     * fromValue method.
     *
     * @param mixed $just
     * @param mixed $nothing
     *
     * @return object Maybe
     */
    public static function fromValue($just, $nothing = null): self
    {
        return $just !== $nothing ? self::just($just) : self::nothing();
    }

    /**
     * lift method.
     *
     * @param callable $fn
     *
     * @return callable
     */
    public static function lift(callable $fn): callable
    {
        return function () use ($fn) {
            if (
                f\fold(
                    fn(bool $status, Maybe $val) => $val->isNothing() ? false : $status,
                    func_get_args($fn),
                    true
                )
            ) {
                $args = f\map(
                    fn(Maybe $maybe) => $maybe->getOrElse(null),
                    func_get_args($fn)
                );

                return self::just(call_user_func($fn, ...$args));
            }

            return self::nothing();
        };
    }

    /**
     * of method.
     *
     * @param mixed $value
     *
     * @return object Maybe
     */
    abstract public static function of($value): self;

    /**
     * getJust method.
     *
     * @abstract
     */
    abstract public function getJust();

    /**
     * getNothing method.
     *
     * @abstract
     */
    abstract public function getNothing();

    /**
     * isJust method.
     *
     * @abstract
     *
     * @return bool
     */
    abstract public function isJust(): bool;

    /**
     * isNothing method.
     *
     * @abstract
     *
     * @return bool
     */
    abstract public function isNothing(): bool;

    /**
     * flatMap method.
     *
     * @abstract
     *
     * @param callable $fn
     *
     * @return mixed $value
     */
    abstract public function flatMap(callable $fn);

    /**
     * ap method.
     *
     * @abstract
     *
     * @param Maybe $app
     *
     * @return object Maybe
     */
    abstract public function ap(M\Monadic $app): M\Monadic;

    /**
     * getOrElse method.
     *
     * @abstract
     *
     * @param mixed $default
     *
     * @return mixed $value
     */
    abstract public function getOrElse($default);

    /**
     * map method.
     *
     * @abstract
     *
     * @param callable $fn
     *
     * @return object Maybe
     */
    abstract public function map(callable $function): M\Monadic;

    /**
     * bind method.
     *
     * @abstract
     *
     * @param callable $function
     *
     * @return object Maybe
     */
    abstract public function bind(callable $function): M\Monadic;

    /**
     * filter method.
     *
     * @abstract
     *
     * @param callable $fn
     *
     * @return object Maybe
     */
    abstract public function filter(callable $fn): self;

    /**
     * orElse method.
     *
     * @abstract
     *
     * @param Maybe $value
     *
     * @return object Maybe
     */
    abstract public function orElse(self $value): self;
}
