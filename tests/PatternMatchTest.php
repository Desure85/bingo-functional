<?php

namespace Chemem\Bingo\Functional\Tests;

use Chemem\Bingo\Functional\Algorithms as A;
use Chemem\Bingo\Functional\Functors\Monads\IO;
use Chemem\Bingo\Functional\Functors\Monads\State;
use Chemem\Bingo\Functional\PatternMatching as PM;
use PHPUnit\Framework\TestCase;

class PatternMatchTest extends TestCase
{
    public static function letInFunc(array $_let, array $_in, callable $action)
    {
        $list = range(1, 10);
        $let = PM\letIn($_let, $list);
        return $let($_in, $action);
    }

    public function testGetNumConditionsFunctionOutputsArrayOfArities()
    {
        $numConditions = PM\getNumConditions(['(a:b:_)', '(a:_)', '_']);

        $this->assertEquals(
            $numConditions,
            [
                '(a:b:_)' => 2,
                '(a:_)'   => 1,
                '_'       => 0,
            ]
        );
    }

    public function testMatchFunctionComputesMatches()
    {
        $match = PM\match(
            [
                '(x:y:_)'   => fn(int $divd, int $divs) => $divd / $divs,
                '(x:_)'     => fn($divd) => $divd / 2,
                '_'         => fn() => 1,
            ]
        );

        $result = $match([10, 5]);

        $this->assertEquals($result, 2);
    }

    public function testEvalStringPatternEvaluatesStrings()
    {
        $strings = A\partialLeft(
            PM\evalStringPattern,
            [
                '"foo"' => fn() => 'foo',
                '"bar"' => fn() => 'bar',
                '_'     => fn() => 'undefined',
            ]
        );

        $this->assertEquals($strings('foo'), 'foo');
        $this->assertEquals($strings('baz'), 'undefined');
    }

    public function testEvalStringPatternEvaluatesNumbers()
    {
        $numbers = A\partialLeft(
            PM\evalStringPattern,
            [
                '"1"'   => fn() => 'first',
                '"2"'   => fn() => 'second',
                '_'     => fn() => 'undefined',
            ]
        );

        $this->assertEquals($numbers(1), 'first');
        $this->assertEquals($numbers(24), 'undefined');
    }

    public function testArrayPatternEvaluatesArrayPatterns()
    {
        $patterns = A\partialLeft(
            PM\evalArrayPattern,
            [
                '["foo", "bar", baz]'   => fn(string $baz) => strtoupper($baz),
                '["foo", "bar"]'        => fn() => 'foo-bar',
                '_'                     => fn() => 'undefined',
            ]
        );

        $this->assertEquals($patterns(['foo', 'bar']), 'foo-bar');
        $this->assertEquals($patterns(['foo', 'bar', 'cat']), 'CAT');
        $this->assertEquals($patterns([]), 'undefined');
    }

    public function testPatternMatchFunctionPerformsSingleValueSensitiveMatch()
    {
        $pattern = PM\patternMatch(
            [
                '"foo"' => fn() => strtoupper('foo'),
                '"12"'  => fn() => pow(12, 2),
                '_'     => fn() => 'undefined',
            ],
            'foo'
        );

        $this->assertEquals($pattern, 'FOO');
    }

    public function testPatternMatchFunctionPerformsMultipleValueSensitiveMatch()
    {
        $pattern = PM\patternMatch(
            [
                '["foo", "bar"]'        => fn() => strtoupper('foo-bar'),
                '["foo", "bar", baz]'   => fn(string $baz) => lcfirst(strtoupper($baz)),
                '_'                     => fn() => 'undefined',
            ],
            explode('/', 'foo/bar/functional')
        );

        $this->assertEquals($pattern, 'fUNCTIONAL');
    }

    public function testEvalObjectPatternMatchesObjects()
    {
        $evalObject = PM\evalObjectPattern(
            [
                IO::class       => fn() => 'IO monad',
                State::class    => fn() =>'State monad',
                '_'             => fn() =>'NaN',
            ],
            IO::of(fn() => 12)
        );

        $this->assertEquals('IO monad', $evalObject);
    }

    public function testEvalArrayPatternMatchesListPatternWithWildcard()
    {
        $pattern = PM\evalArrayPattern(
            [
                '[_, "chemem"]' => fn() => 'chemem',
                '_'             => fn() => 'don\'t care',
            ],
            ['func', 'chemem']
        );

        $this->assertEquals('chemem', $pattern);
    }

    public function testEvalArrayPatternEvaluatesIrregularWildcardPatterns()
    {
        $result = PM\evalArrayPattern(
            [
                '["ask", _, "mike"]'    => fn() => 'G.O.A.T',
                '_'                     => fn() => 'not the greatest',
            ],
            ['ask', 'uncle', 'mike']
        );

        $this->assertEquals('G.O.A.T', $result);
    }

    public function testLetInDestructuresByPatternMatching()
    {
        $list = range(1, 10);
        $let = PM\letIn(['a', 'b', 'c'], $list);
        $_in = $let(['c'], function (int $c) {
            return $c * 10;
        });

        $this->assertInstanceOf(\Closure::class, $let);
        $this->assertEquals(30, $_in);
    }

    public function testLetInFunctionAcceptsWildcardParameters()
    {
        $letIn = self::letInFunc(
            ['a', '_', '_', 'b'], 
            ['a', 'b'], 
            fn($a, $b) => $a + $b
        );

        $this->assertEquals(5, $letIn);
        $this->assertInternalType('integer', $letIn);
    }
}
