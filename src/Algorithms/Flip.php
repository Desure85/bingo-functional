<?php

/**
 * Flip function.
 *
 * flip :: (a -> b -> c) -> b -> a -> c
 *
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

namespace Chemem\Bingo\Functional\Algorithms;

const flip = 'Chemem\\Bingo\\Functional\\Algorithms\\flip';

function flip(callable $function)
{
    return fn(...$args) => $function(...array_reverse($args));
}
