<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Validator\String;

use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Rule\Validator\String\MaxLengthRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MaxLengthRule::class)]
final class MaxLengthRuleTest extends TestCase
{
    private MaxLengthRule $rule;

    protected function setUp(): void
    {
        $this->rule = new MaxLengthRule();
    }

    /**
     * @param list<string> $parameters
     */
    #[DataProvider('validMaxLengthDataProvider')]
    public function testValidMaxLength(mixed $value, array $parameters): void
    {
        $this->rule->validate($value, $parameters);
        $this->addToAssertionCount(1);
    }

    /**
     * @param list<string> $parameters
     */
    #[DataProvider('invalidMaxLengthDataProvider')]
    public function testInvalidMaxLength(mixed $value, array $parameters): void
    {
        $this->expectException(ValidationException::class);
        $this->rule->validate($value, $parameters);
    }

    public function testMissingParameter(): void
    {
        $this->expectException(ValidationException::class);
        $this->rule->validate('test', []);
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function validMaxLengthDataProvider(): array
    {
        return [
            'exact_length_string' => ['hello', [5]],
            'shorter_string' => ['hi', [5]],
            'empty_string' => ['', [5]],
            'unicode_string' => ['josé', [5]],
        ];
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function invalidMaxLengthDataProvider(): array
    {
        return [
            'long_string' => ['hello world', [5]],
            'non_string' => [123, [3]],
        ];
    }
}
