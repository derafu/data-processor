<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Caster;

use Derafu\DataProcessor\Exception\CastingException;
use Derafu\DataProcessor\Rule\Caster\FloatRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(FloatRule::class)]
final class FloatRuleTest extends TestCase
{
    private FloatRule $rule;

    protected function setUp(): void
    {
        $this->rule = new FloatRule();
    }

    #[DataProvider('floatCastDataProvider')]
    public function testFloatCast(mixed $input, array $parameters, float $expected): void
    {
        $result = $this->rule->cast($input, $parameters);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('invalidFloatCastDataProvider')]
    public function testInvalidFloatCast(mixed $input): void
    {
        $this->expectException(CastingException::class);
        $this->rule->cast($input);
    }

    public static function floatCastDataProvider(): array
    {
        return [
            'integer' => [123, [], 123.0],
            'float' => [123.45, [], 123.45],
            'numeric_string' => ['123.45', [], 123.45],
            'with_precision' => [123.456789, ['2'], 123.46],
            'boolean_true' => [true, [], 1.0],
            'boolean_false' => [false, [], 0.0],
            'null' => [null, [], 0.0],
            'zero' => ['0', [], 0.0],
            'negative' => [-123.45, [], -123.45],
        ];
    }

    public static function invalidFloatCastDataProvider(): array
    {
        return [
            'non_numeric_string' => ['hello'],
            'mixed_string' => ['123.45abc'],
            'array' => [[1,2,3]],
        ];
    }
}
