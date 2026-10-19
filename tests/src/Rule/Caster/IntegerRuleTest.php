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
use Derafu\DataProcessor\Rule\Caster\IntegerRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(IntegerRule::class)]
final class IntegerRuleTest extends TestCase
{
    private IntegerRule $rule;

    protected function setUp(): void
    {
        $this->rule = new IntegerRule();
    }

    #[DataProvider('integerCastDataProvider')]
    public function testIntegerCast(mixed $input, int $expected): void
    {
        $result = $this->rule->cast($input);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('invalidIntegerCastDataProvider')]
    public function testInvalidIntegerCast(mixed $input): void
    {
        $this->expectException(CastingException::class);
        $this->rule->cast($input);
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function integerCastDataProvider(): array
    {
        return [
            'integer' => [123, 123],
            'float_without_decimals' => [123.0, 123],
            'numeric_string_with_zero_decimals' => ['1.0', 1],
            'numeric_string_with_exponent' => ['1e2', 100],
            'negative_numeric_string' => ['-5', -5],
            'large_numeric_string' => ['9007199254740993', 9007199254740993],
            'numeric_string' => ['123', 123],
            'boolean_true' => [true, 1],
            'boolean_false' => [false, 0],
            'null' => [null, 0],
            'zero_string' => ['0', 0],
            'negative' => [-123, -123],
        ];
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function invalidIntegerCastDataProvider(): array
    {
        return [
            'float_with_decimals' => [123.45],
            'numeric_string_with_decimals' => ['1.9'],
            'nan' => [NAN],
            'infinite' => [INF],
            'numeric_string_out_of_range' => ['1e30'],
            'integer_string_out_of_range' => ['9223372036854775808'],
            'non_numeric_string' => ['hello'],
            'mixed_string' => ['123abc'],
            'array' => [[1,2,3]],
            'object' => [new \stdClass()],
        ];
    }
}
