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

    public static function integerCastDataProvider(): array
    {
        return [
            'integer' => [123, 123],
            'float' => [123.45, 123],
            'numeric_string' => ['123', 123],
            'boolean_true' => [true, 1],
            'boolean_false' => [false, 0],
            'null' => [null, 0],
            'zero_string' => ['0', 0],
            'negative' => [-123, -123],
        ];
    }

    public static function invalidIntegerCastDataProvider(): array
    {
        return [
            'non_numeric_string' => ['hello'],
            'mixed_string' => ['123abc'],
            'array' => [[1,2,3]],
        ];
    }
}
