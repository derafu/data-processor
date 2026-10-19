<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Validator\Array;

use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Rule\Validator\Array\MinContainsRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MinContainsRule::class)]
final class MinContainsRuleTest extends TestCase
{
    /**
     * @param list<string> $parameters
     */
    #[DataProvider('dataProvider')]
    public function testValidate(mixed $value, array $parameters, ?string $message): void
    {
        $rule = new MinContainsRule();

        if ($message !== null) {
            $this->expectException(ValidationException::class);
            $this->expectExceptionMessage($message);
        }

        $rule->validate($value, $parameters);

        if ($message === null) {
            $this->addToAssertionCount(1);
        }
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function dataProvider(): array
    {
        return [
            'enough' => [['a', 'b', 'x'], ['2', 'a', 'b'], null],
            'exactly_the_minimum' => [['a', 'a'], ['2', 'a'], null],
            'zero_always_passes' => [['x'], ['0', 'a'], null],
            'not_enough' => [['a', 'x'], ['2', 'a', 'b'], 'Array must contain at least 2 of: a, b.'],
            'not_an_array' => ['x', ['1', 'a'], 'Value must be an array.'],
            'without_number' => [['a'], [], 'Minimum number of items must be specified.'],
            'empty_number' => [['a'], ['', 'a'], 'Minimum number of items must be specified.'],
            'number_that_is_not_valid' => [['a'], ['-1', 'a'], 'Invalid parameter: -1'],
            'without_values' => [['a'], ['1'], 'Choices must be specified.'],
        ];
    }
}
