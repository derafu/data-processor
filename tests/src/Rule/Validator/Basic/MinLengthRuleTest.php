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
use Derafu\DataProcessor\Rule\Validator\String\MinLengthRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MinLengthRule::class)]
final class MinLengthRuleTest extends TestCase
{
    private MinLengthRule $rule;

    protected function setUp(): void
    {
        $this->rule = new MinLengthRule();
    }

    /**
     * @param list<string> $parameters
     */
    #[DataProvider('validMinLengthDataProvider')]
    public function testValidMinLength(mixed $value, array $parameters): void
    {
        $this->rule->validate($value, $parameters);
        $this->addToAssertionCount(1);
    }

    /**
     * @param list<string> $parameters
     */
    #[DataProvider('invalidMinLengthDataProvider')]
    public function testInvalidMinLength(mixed $value, array $parameters): void
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
    public static function validMinLengthDataProvider(): array
    {
        return [
            'exact_length_string' => ['hello', [5]],
            'longer_string' => ['hello world', [5]],
            'unicode_string' => ['josé', [4]],
        ];
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function invalidMinLengthDataProvider(): array
    {
        return [
            'short_string' => ['hi', [3]],
            'empty_string' => ['', [1]],
            'non_string' => [123, [3]],
        ];
    }
}
