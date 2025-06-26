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
use Derafu\DataProcessor\Rule\Validator\String\Base64Rule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Base64Rule::class)]
final class Base64RuleTest extends TestCase
{
    private Base64Rule $rule;

    protected function setUp(): void
    {
        $this->rule = new Base64Rule();
    }

    #[DataProvider('base64DataProvider')]
    public function testBase64(mixed $value, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $this->rule->validate($value);

        if ($shouldPass) {
            $this->assertTrue(true);
        }
    }

    public static function base64DataProvider(): array
    {
        return [
            'valid_base64' => ['SGVsbG8gV29ybGQ=', true], // "Hello World"
            'valid_empty' => ['', true],
            'invalid_chars' => ['SGVsbG8gV29ybGQ!', false],
            //'invalid_padding' => ['SGVsbG8gV29ybGQ', false],
            'non_string' => [123, false],
        ];
    }
}
