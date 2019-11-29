<?php

/**
 * every function.
 *
 * every :: [a] -> (b -> Bool) -> c
 *
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

namespace Chemem\Bingo\Functional\Algorithms;

const every = 'Chemem\\Bingo\\Functional\\Algorithms\\every';

function every(array $collection, callable $func): bool
{
    $everyFn = compose(
        partialLeft(filter, $func),
        fn(array $result): bool => count($result) == count($collection)
    );

    return $everyFn($collection);
}
