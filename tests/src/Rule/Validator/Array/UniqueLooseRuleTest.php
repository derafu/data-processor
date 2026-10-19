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
use Derafu\DataProcessor\Rule\Validator\Array\UniqueLooseRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UniqueLooseRule::class)]
final class UniqueLooseRuleTest extends TestCase
{
    #[DataProvider('uniqueLooseDataProvider')]
    public function testUniqueLoose(mixed $value, ?string $message): void
    {
        $rule = new UniqueLooseRule();

        if ($message !== null) {
            $this->expectException(ValidationException::class);
            $this->expectExceptionMessage($message);
        }

        $rule->validate($value);

        if ($message === null) {
            $this->addToAssertionCount(1);
        }
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function uniqueLooseDataProvider(): array
    {
        $duplicates = 'Array must contain unique values.';

        return [
            'unique_numbers' => [[1, 2, 3], null],
            'unique_strings' => [['a', 'b', 'c'], null],
            'empty_array' => [[], null],
            'duplicate_strings' => [['a', 'b', 'a'], $duplicates],
            'numeric_strings_with_different_text' => [['1e1', '10'], $duplicates],
            'same_text_and_number_with_different_type' => [['1', 1], $duplicates],
            'non_array' => ['string', 'Value must be an array.'],
        ];
    }
}
