<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Validator\String;

use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Rule\Validator\String\RequiredRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RequiredRule::class)]
final class RequiredRuleTest extends TestCase
{
    private RequiredRule $rule;

    protected function setUp(): void
    {
        $this->rule = new RequiredRule();
    }

    #[DataProvider('validRequiredDataProvider')]
    public function testValidRequired(mixed $value): void
    {
        $this->rule->validate($value);
        $this->assertTrue(true);
    }

    #[DataProvider('invalidRequiredDataProvider')]
    public function testInvalidRequired(mixed $value): void
    {
        $this->expectException(ValidationException::class);
        $this->rule->validate($value);
    }

    public static function validRequiredDataProvider(): array
    {
        return [
            'non_empty_string' => ['hello'],
            'zero_string' => ['0'],
            'integer' => [0],
            'float' => [0.0],
            'boolean_false' => [false],
            'array_with_items' => [['item']],
            'object' => [(object)['key' => 'value']],
        ];
    }

    public static function invalidRequiredDataProvider(): array
    {
        return [
            'null' => [null],
            'empty_string' => [''],
        ];
    }
}
