<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Support;

use Derafu\DataProcessor\Support\Value;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Value::class)]
final class ValueTest extends TestCase
{
    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function valuesProvider(): array
    {
        return [
            'null' => [null, true],
            'empty_string' => ['', true],
            'empty_array' => [[], true],
            'zero_int' => [0, false],
            'zero_float' => [0.0, false],
            'zero_string' => ['0', false],
            'false' => [false, false],
            'space' => [' ', false],
            'string' => ['a', false],
            'array' => [[null], false],
            'object' => [new stdClass(), false],
        ];
    }

    #[DataProvider('valuesProvider')]
    public function testIsEmpty(mixed $value, bool $expected): void
    {
        $this->assertSame($expected, Value::isEmpty($value));
    }
}
