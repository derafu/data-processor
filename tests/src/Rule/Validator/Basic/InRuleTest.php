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
use Derafu\DataProcessor\Rule\Validator\Array\InRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InRule::class)]
final class InRuleTest extends TestCase
{
    private InRule $rule;

    protected function setUp(): void
    {
        $this->rule = new InRule();
    }

    /**
     * @param list<string> $parameters
     */
    #[DataProvider('validInDataProvider')]
    public function testValidIn(mixed $value, array $parameters): void
    {
        $this->rule->validate($value, $parameters);
        $this->addToAssertionCount(1);
    }

    /**
     * @param list<string> $parameters
     */
    #[DataProvider('invalidInDataProvider')]
    public function testInvalidIn(mixed $value, array $parameters): void
    {
        $this->expectException(ValidationException::class);
        $this->rule->validate($value, $parameters);
    }

    public function testMissingChoices(): void
    {
        $this->expectException(ValidationException::class);
        $this->rule->validate('test', []);
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function validInDataProvider(): array
    {
        return [
            'string_choice' => ['apple', ['apple', 'banana', 'orange']],
            'integer_choice' => [1, [1, 2, 3]],
            'integer_as_text_choice' => [2, ['1', '2', '3']],
            'text_as_integer_choice' => ['2', [1, 2, 3]],
            'float_choice' => [2.0, ['1', '2', '3']],
            'array_choice' => [['a', 'b'], ['a', 'b', 'c']],
            'empty_array_choice' => [[], ['a', 'b']],
        ];
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function invalidInDataProvider(): array
    {
        return [
            'invalid_string' => ['grape', ['apple', 'banana', 'orange']],
            'invalid_integer' => [4, [1, 2, 3]],
            'boolean' => [true, ['1', '2', '3']],
            'null' => [null, ['apple', '', 'banana']],
            'invalid_array' => [['a', 'z'], ['a', 'b']],
            'array_with_invalid_type' => [['a', true], ['a', '1']],
            'nested_array' => [[['a']], ['a']],
            'object' => [new \stdClass(), ['a']],
        ];
    }
}
