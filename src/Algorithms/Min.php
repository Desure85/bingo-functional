<?php

/**
 * min function.
 *
 * min :: [a, b] -> a
 *
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

namespace Chemem\Bingo\Functional\Algorithms;

const min = 'Chemem\\Bingo\\Functional\\Algorithms\\min';

function min(array $collection): float
{
    return fold(
        fn(float $acc, float $val) => $val < $acc ? $val : $acc,
        $collection,
        head($collection)
    );
}
