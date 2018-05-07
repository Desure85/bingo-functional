<?php

namespace Chemem\Bingo\Functional\Algorithms;

const fromPairs = 'Chemem\\Bingo\\Functional\\Algorithms\\fromPairs';

function fromPairs(array $collection) : array
{
    $pairs = map(
        function ($value) {
            list($key, $val) = is_array($value) && count($value) == 2 ?
                $value :
                [null, null];

            return [$key => $val];
        },
        $collection
    );

    return array_merge(...$pairs);
}