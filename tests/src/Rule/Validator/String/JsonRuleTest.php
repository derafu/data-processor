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
use Derafu\DataProcessor\Rule\Validator\String\JsonRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonRule::class)]
final class JsonRuleTest extends TestCase
{
    private JsonRule $rule;

    protected function setUp(): void
    {
        $this->rule = new JsonRule();
    }

    #[DataProvider('jsonDataProvider')]
    public function testJson(mixed $value, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $this->rule->validate($value);

        if ($shouldPass) {
            $this->assertTrue(true);
        }
    }

    public static function jsonDataProvider(): array
    {
        return [
            'valid_object' => ['{"name":"John","age":30}', true],
            'valid_array' => ['[1,2,3]', true],
            'valid_string' => ['"Hello"', true],
            'valid_number' => ['123', true],
            'valid_boolean' => ['true', true],
            'valid_null' => ['null', true],
            'invalid_syntax' => ['{name:"John"}', false],
            'incomplete_object' => ['{"name":"John"', false],
            'non_string' => [123, false],
        ];
    }
}
