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
use Derafu\DataProcessor\Rule\Validator\Array\MaxContainsRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MaxContainsRule::class)]
final class MaxContainsRuleTest extends TestCase
{
    /**
     * @param list<string> $parameters
     */
    #[DataProvider('dataProvider')]
    public function testValidate(mixed $value, array $parameters, ?string $message): void
    {
        $rule = new MaxContainsRule();

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
            'within_the_limit' => [['a', 'x'], ['1', 'a', 'b'], null],
            'exactly_the_limit' => [['a', 'b'], ['2', 'a', 'b'], null],
            'none_with_zero' => [['x'], ['0', 'a'], null],
            'too_many' => [['a', 'b'], ['1', 'a', 'b'], 'Array must not contain more than 1 of: a, b.'],
            'not_an_array' => ['x', ['1', 'a'], 'Value must be an array.'],
            'without_number' => [['a'], [], 'Maximum number of items must be specified.'],
            'number_that_is_not_valid' => [['a'], ['x', 'a'], 'Invalid parameter: x'],
            'without_values' => [['a'], ['1'], 'Choices must be specified.'],
        ];
    }
}
