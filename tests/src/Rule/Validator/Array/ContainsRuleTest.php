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
use Derafu\DataProcessor\Rule\Validator\Array\ContainsRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContainsRule::class)]
final class ContainsRuleTest extends TestCase
{
    /**
     * @param list<string> $parameters
     */
    #[DataProvider('dataProvider')]
    public function testValidate(mixed $value, array $parameters, ?string $message): void
    {
        $rule = new ContainsRule();

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
            'one_of_the_values' => [['a', 'terms'], ['terms'], null],
            'one_of_several' => [['a', 'b'], ['x', 'b'], null],
            'number_as_text' => [[1, 2.0], ['2'], null],
            'none_of_the_values' => [['a', 'b'], ['terms'], 'Array must contain one of: terms.'],
            'several_values_in_the_message' => [['a'], ['x', 'y'], 'Array must contain one of: x, y.'],
            'empty_array' => [[], ['x'], 'Array must contain one of: x.'],
            'booleans_and_null_are_not_values' => [[true, null, [1]], ['1'], 'Array must contain one of: 1.'],
            'not_an_array' => ['x', ['x'], 'Value must be an array.'],
            'without_values' => [['x'], [], 'Choices must be specified.'],
        ];
    }
}
