<?php

/**
 * List monad.
 *
 * @author Lochemem Bruno Michael
 * @license Apache 2.0
 */

namespace Chemem\Bingo\Functional\Functors\Monads;

use \Chemem\Bingo\Functional\Algorithms as f;

class ListMonad implements Monadic
{
    const of = __CLASS__ . '::of';

    /**
     * @var array The collection to transform
     */
    private array $collection;

    /**
     * ListMonad constructor.
     *
     * @param mixed $collection
     */
    public function __construct(array $collection)
    {
        $this->collection = $collection;
    }

    /**
     * of method.
     *
     * @param mixed $collection
     *
     * @return object ListMonad
     */
    public static function of($collection): self
    {
        return new static(is_array($collection) ? $collection : [$collection]);
    }

    /**
     * ap method.
     *
     * @param object ListMonad
     *
     * @return object ListMonad
     */
    public function ap(Monadic $app): Monadic
    {
        $list = $this->extract();

        $result = compose(
            partialLeft(f\filter, 'is_callable'),
            partialLeft(f\map, function ($func) use ($list) {
                $app = fn(array $acc = []) => f\mapDeep($func, $list);

                return $app();
            }),
            fn($result) => f\extend($list, ...$result)
        );

        return new static($result($app->extract()));
    }

    /**
     * bind method.
     *
     * @param callable $function
     *
     * @return object ListMonad
     */
    public function bind(callable $function): Monadic
    {
        $concat = f\compose(
            fn(array $list) => f\fold(function ($acc, $item) use ($function) {
                $acc[] = $function($item)->extract();

                return $acc;
            }, $list, []),
            f\partial('array_merge', $this->collection)
        );

        return self::of(f\flatten($concat($this->collection)));
    }

    /**
     * map method.
     *
     * @param callable $function
     *
     * @return object ListMonad
     */
    public function map(callable $function): Monadic
    {
        return $this->bind(fn($list) => self::of($function($list)));
    }

    /**
     * flatMap method.
     *
     * @param callable $function
     *
     * @return mixed $result
     */
    public function flatMap(callable $function)
    {
        return $this
            ->map($function)
            ->extract();
    }

    /**
     * extract method.
     *
     * @return array $collection
     */
    public function extract(): array
    {
        return f\flatten($this->collection);
    }
}
