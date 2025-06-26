<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Transformer;

use Derafu\DataProcessor\Exception\TransformationException;
use Derafu\DataProcessor\Rule\Transformer\Numeric\RoundRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoundRule::class)]
final class RoundRuleTest extends TestCase
{
    private RoundRule $rule;

    protected function setUp(): void
    {
        $this->rule = new RoundRule();
    }

    #[DataProvider('roundDataProvider')]
    public function testRound(mixed $input, array $parameters, mixed $expected): void
    {
        $result = $this->rule->transform($input, $parameters);
        $this->assertSame($expected, $result);
    }

    public function testNonNumericValue(): void
    {
        $this->expectException(TransformationException::class);

        $value = 'abc';
        $result = $this->rule->transform($value);
    }

    public static function roundDataProvider(): array
    {
        return [
            'integer_no_precision' => [
                123,
                [],
                123,
            ],
            'float_no_precision' => [
                123.456,
                [],
                123,
            ],
            'float_one_decimal' => [
                123.456,
                ['1'],
                123.5,
            ],
            'float_two_decimals' => [
                123.456,
                ['2'],
                123.46,
            ],
            'negative_float' => [
                -123.456,
                ['2'],
                -123.46,
            ],
            'string_numeric' => [
                '123.456',
                ['2'],
                123.46,
            ],
            'rounding_up' => [
                1.5,
                [],
                2,
            ],
            'rounding_down' => [
                1.4,
                [],
                1,
            ],
        ];
    }
}
