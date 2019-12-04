<?php

/**
 * Pattern matching functions.
 *
 * @author Lochemem Bruno Michael
 * @license Apache-2.0
 */

namespace Chemem\Bingo\Functional\PatternMatching;

use Chemem\Bingo\Functional\Algorithms as A;

/**
 * match function.
 *
 * @param array $options
 *
 * @return callable $matchCons
 */

const match = 'Chemem\\Bingo\\Functional\\PatternMatching\\match';

function match(array $options): callable
{
    $matchFn = fn(array $options): array => array_key_exists('_', $options) ? $options : [
        '_' => fn() => false
    ];

    $conditionGen = A\compose(
        $matchFn,
        A\partialRight('array_filter', 'is_callable'),
        'array_keys',
        getNumConditions
    );

    return function (array $values) use ($options, $matchFn, $conditionGen) {
        $valCount = count($values);

        $check = A\compose(
            $conditionGen,
            A\partialLeft(A\filter, fn(int $count): bool => $count == $valCount),
            A\head
        );

        return $check($options) > 0 ?
            call_user_func_array(
                $options[A\indexOf($conditionGen($options), $valCount)],
                $values
            ) :
            call_user_func($matchFn($options)['_']);
    };
}

/**
 * getNumConditions function.
 *
 * @param array $conditions
 *
 * @return array $matches
 */
const getNumConditions = 'Chemem\\Bingo\\Functional\\PatternMatching\\getNumConditions';

function getNumConditions(array $conditions)
{
    $checkOpt = fn($opt) => preg_match('/([_])+/', $opt) ? $opt : '_';

    $extr = A\map(
        function (string $condition) use ($checkOpt) {
            $opts = A\compose(
                $checkOpt,
                A\partialLeft('preg_replace', '/([(\)])+/', ''),
                A\partialLeft('explode', ':'),
                A\partialLeft(A\filter, fn($val) => $val !== '_'),
                'count'
            );

            return [$condition => $opts($condition)];
        },
        $conditions
    );

    return array_merge(...$extr);
}

/**
 * patternMatch function.
 *
 * patternMatch :: [a, b] -> a -> (a())
 *
 * @param array $patterns
 * @param mixed $value
 *
 * @return mixed $result
 */
const patternMatch = 'Chemem\\Bingo\\Functional\\PatternMatching\\patternMatch';

function patternMatch(array $patterns, $value)
{
    switch ($value) {
        case is_object($value):
            return evalObjectPattern($patterns, $value);
            break;

        case is_array($value):
            return evalArrayPattern($patterns, $value);
            break;

        default:
            return evalStringPattern($patterns, $value);
            break;
    }
}

/**
 * evalArrayPattern function.
 *
 * evalArrayPattern :: [a, b] -> [a] -> (a())
 *
 * @param array $patterns
 * @param array $value
 *
 * @return mixed $result
 */
const evalArrayPattern = 'Chemem\\Bingo\\Functional\\PatternMatching\\evalArrayPattern';

function evalArrayPattern(array $patterns, array $comp)
{
    $evaluate = A\compose(
        'array_keys',
        A\partial(A\filter, fn($pattern): bool => substr($pattern, 0, 1) == '[' && substr($pattern, -1) == ']'),
        function (array $pttnKeys) {
            $extract = A\compose(
                A\partialLeft('str_replace', '[', ''),
                A\partialLeft('str_replace', ']', ''),
                A\partialLeft('str_replace', ' ', ''),
                A\partialLeft('explode', ', '),
                fn(array $tokens) => array_merge(...array_map(fn($token) => A\fold(function (
                    $acc,
                $tkn
                ) {
                    $acc[] = preg_match('/[\"]+/', $tkn) ? A\concat('*', '', str_replace('"', '', $tkn)) : $tkn;
                    return $acc;
                },
                explode(',', $token),
                []),
                $tokens))
            );

            return array_combine($pttnKeys, A\map($extract, $pttnKeys));
        },
        function (array $patterns) use ($comp) {
            $cmpCount = count($comp);
            $filter = A\partialRight(
                'array_filter',
                fn($pttn): bool => count($pttn) == $cmpCount
            );

            return $filter($patterns);
        },
        function (array $patterns) use ($comp) {
            $compLen = count($comp);

            $list = array_map(function ($pttns) use ($comp, $compLen) {
                $keys = array_map(A\partial('str_replace', '*', ''), $pttns);

                $intersect = array_intersect_assoc($keys, $comp);

                return A\extend($pttns, ['intersect' => $intersect]);
            }, $patterns);

            return $list;
        },
        function (array $patterns) use ($comp) {
            return array_filter($patterns, function ($pttn) use ($comp) {
                $raw = A\dropRight(array_values($pttn), 1);
                $keys = array_map(A\partial('str_replace', '*', ''), $raw);

                return !empty($pttn['intersect']) && in_array('_', $keys) && end($keys) == end($comp) ||
                    !empty($pttn['intersect']) && !preg_match('/[\*\_]+/', end($raw)) ||
                    count($pttn['intersect']) == count($comp);
            });
        },
        function (array $pattern) use ($comp, $patterns) {
            $funcKey = !empty($pattern) ? A\head(array_keys($pattern)) : '_';
            $pttn = !empty($pattern) ? A\head($pattern) : [];
            $args = A\fold(function ($acc, $val) use ($comp, $pttn) {
                if (is_string($val) && !preg_match('/[\"\*]+/', $val)) {
                    $acc[] = $comp[A\indexOf($pttn, $val)];
                }

                return $acc;
            }, $pttn, []);

            return !empty($args) ? $patterns[$funcKey](...$args) : $patterns[$funcKey]();
        }
    );

    return $evaluate($patterns);
}

/**
 * evalStringPattern function.
 *
 * evalStringPattern :: [a, b] -> a -> (a())
 *
 * @param array  $patterns
 * @param string $value
 *
 * @return mixed $result
 */
const evalStringPattern = 'Chemem\\Bingo\\Functional\\PatternMatching\\evalStringPattern';

function evalStringPattern(array $patterns, string $value)
{
    $evalPattern = A\compose(
        'array_keys',
        A\partialLeft(A\filter, fn($val): bool => is_string($val) && preg_match('/([\"]+)/', $val)),
        A\partialLeft(A\map, function ($val) use ($value) {
            $evaluate = A\compose(
                A\partialLeft('str_replace', '"', ''),
                function ($val) {
                    $valType = gettype($val);

                    return $valType == 'integer' ?
                        (int) $val :
                        ($valType == 'double' ? (float) $val : $val);
                },
                function ($val) use ($value) {
                    if (empty($value)) {
                        return '_';
                    }

                    return $val == $value ? A\concat('"', '', $val, '') : '_';
                }
            );

            return $evaluate($val);
        }),
        A\partialLeft(A\filter, fn($val): bool => $val !== '_'),
        fn($match) => !empty($match) ? A\head($match) : '_',
        function ($match) use ($patterns) {
            $valType = A\compose('array_values', A\isArrayOf)($patterns);

            return $valType == 'object' ?
                $patterns[$match] :
                ['_' => A\constantFunction(false)];
        }
    )($patterns);

    return call_user_func($evalPattern);
}

/**
 * evalObjectPattern function.
 *
 * evalObjectPattern :: [a, b] -> b -> (b())
 *
 * @param array  $patterns
 * @param object $value
 *
 * @return mixed $result
 */
const evalObjectPattern = 'Chemem\\Bingo\\Functional\\PatternMatching\\evalObjectPattern';

function evalObjectPattern(array $patterns, $value)
{
    $valObj = get_class($value);

    $eval = A\compose(
        'array_keys',
        A\partialLeft(A\filter, fn($val): bool => is_string($val) && preg_match('/([a-zA-Z]+)/', $val)),
        A\partialLeft(A\filter, fn($classStr): bool => class_exists($classStr) && $classStr == $valObj),
        A\head,
        fn(string $match) => !empty($match) && !is_null($match) ? A\identity($match) : A\identity('_'),
        fn(string $key) => call_user_func($key == '_' ? isset($patterns['_']) ? A\identity($patterns['_']) : constantFunction(false) : A\identity($patterns[$key]))
    );

    return $eval($patterns);
}

/**
 *
 * letIn function
 *
 * letIn :: [a] -> [a, b] -> ([a, b] -> c)
 *
 * @param array $params
 * @param array $list
 * @return callable
 */

const letIn = 'Chemem\\Bingo\\Functional\\PatternMatching\\letIn';

function letIn(array $params, array $list): callable
{
    $patterns = array_merge(...array_map(function ($param, $val, $acc = []) {
        if ($param == '_' || is_null($param)) {
            $acc[] = $val;
        }
        
        return [
            is_null($param) ?
                '_' :
                '"' . $param . '"' => fn() => !is_null($param) ? $val : $acc
        ];
    }, $params, $list));

    return function (array $params, callable $function) use ($patterns) {
        $values = A\map(A\partial(patternMatch, $patterns), $params);
        
        return $function(...$values);
    };
}
