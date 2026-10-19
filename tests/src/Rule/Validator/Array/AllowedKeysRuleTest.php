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
use Derafu\DataProcessor\Rule\Validator\Array\AllowedKeysRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AllowedKeysRule::class)]
final class AllowedKeysRuleTest extends TestCase
{
    /**
     * @param list<string> $parameters
     */
    #[DataProvider('dataProvider')]
    public function testValidate(mixed $value, array $parameters, ?string $message): void
    {
        $rule = new AllowedKeysRule();

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
            'all_allowed' => [['a' => 1, 'b' => 2], ['a', 'b', 'c'], null],
            'empty_array' => [[], ['a'], null],
            'one_not_allowed' => [['a' => 1, 'c' => 2], ['a', 'b'], 'Properties not allowed: c.'],
            'several_not_allowed' => [['a' => 1, 'x' => 2, 'y' => 3], ['a'], 'Properties not allowed: x, y.'],
            'numeric_keys_are_text' => [[1 => 'a'], ['1'], null],
            'numeric_keys_not_allowed' => [['a', 'b'], ['x'], 'Properties not allowed: 0, 1.'],
            'not_an_array' => ['x', ['a'], 'Value must be an array.'],
            'without_keys' => [['a' => 1], [], 'Choices must be specified.'],
        ];
    }
}
