<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
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

    #[DataProvider('validInDataProvider')]
    public function testValidIn(mixed $value, array $parameters): void
    {
        $this->rule->validate($value, $parameters);
        $this->assertTrue(true);
    }

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

    public static function validInDataProvider(): array
    {
        return [
            'string_choice' => ['apple', ['apple', 'banana', 'orange']],
            'integer_choice' => [1, [1, 2, 3]],
            'boolean_choice' => [true, [true, false]],
            'null_choice' => [null, ['apple', null, 'banana']],
        ];
    }

    public static function invalidInDataProvider(): array
    {
        return [
            'invalid_string' => ['grape', ['apple', 'banana', 'orange']],
            'invalid_integer' => [4, [1, 2, 3]],
            'invalid_type' => [1, ['1', '2', '3']], // strict comparison
        ];
    }
}
