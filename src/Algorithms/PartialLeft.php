<?php

/**
 * PartialLeft function.
 *
 * partialLeft :: (a, b) -> (a) b
 *
 * @author Lochemem Bruno Michael
 * @license Apache 2.0
 */

namespace Chemem\Bingo\Functional\Algorithms;

const partialLeft = 'Chemem\\Bingo\\Functional\\Algorithms\\partialLeft';

function partialLeft(callable $fn, ...$args): callable
{
    return fn(...$inner) => call_user_func_array($fn, array_merge($args, $inner));
}

const partial = 'Chemem\\Bingo\\Functional\\Algorithms\\partial';

function partial(callable $func, ...$args): callable
{
    return partialLeft($func, ...$args);
}
