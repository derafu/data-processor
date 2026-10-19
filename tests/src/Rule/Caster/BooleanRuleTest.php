<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Caster;

use Derafu\DataProcessor\Exception\CastingException;
use Derafu\DataProcessor\Rule\Caster\BooleanRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(BooleanRule::class)]
final class BooleanRuleTest extends TestCase
{
    private BooleanRule $rule;

    protected function setUp(): void
    {
        $this->rule = new BooleanRule();
    }

    #[DataProvider('booleanCastDataProvider')]
    public function testBooleanCast(mixed $input, bool $expected): void
    {
        $result = $this->rule->cast($input);
        $this->assertSame($expected, $result);
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function booleanCastDataProvider(): array
    {
        return [
            'true_string' => ['true', true],
            'false_string' => ['false', false],
            'yes_string' => ['yes', true],
            'no_string' => ['no', false],
            'on_string' => ['on', true],
            'off_string' => ['off', false],
            '1_string' => ['1', true],
            '0_string' => ['0', false],
            'true_value' => [true, true],
            'false_value' => [false, false],
            'empty_string' => ['', false],
            'null' => [null, false],
            'integer_1' => [1, true],
            'integer_0' => [0, false],
            'uppercase_string' => ['YES', true],
            'mixed_case_string' => ['Off', false],
        ];
    }

    #[DataProvider('invalidBooleanCastDataProvider')]
    public function testInvalidBooleanCast(mixed $input): void
    {
        $this->expectException(CastingException::class);
        $this->expectExceptionMessage('Cannot cast value to boolean.');

        $this->rule->cast($input);
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function invalidBooleanCastDataProvider(): array
    {
        return [
            'unknown_string' => ['maybe'],
            'integer_2' => [2],
            'negative_integer' => [-1],
            'float' => [1.0],
            'non_empty_array' => [[1]],
            'empty_array' => [[]],
            'object' => [new \stdClass()],
        ];
    }
}
