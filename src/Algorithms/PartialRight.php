<?php

/**
 * PartialRight function.
 *
 * partialRight :: (a, b) -> (b) a
 *
 * @author Lochemem Bruno Michael
 * @license Apache 2.0
 */

namespace Chemem\Bingo\Functional\Algorithms;

const partialRight = 'Chemem\\Bingo\\Functional\\Algorithms\\partialRight';

function partialRight(callable $fn, ...$args): callable
{
    return fn(...$inner) => $fn(...array_reverse(array_merge($args, $inner)));
}
