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
use Derafu\DataProcessor\Rule\Validator\String\NoWhitespaceRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoWhitespaceRule::class)]
final class NoWhitespaceRuleTest extends TestCase
{
    private NoWhitespaceRule $rule;

    protected function setUp(): void
    {
        $this->rule = new NoWhitespaceRule();
    }

    #[DataProvider('noWhitespaceDataProvider')]
    public function testNoWhitespace(mixed $value, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $this->rule->validate($value);

        if ($shouldPass) {
            $this->assertTrue(true); // Reached here without exception
        }
    }

    public static function noWhitespaceDataProvider(): array
    {
        return [
            'no_whitespace' => ['HelloWorld', true],
            'with_space' => ['Hello World', false],
            'with_tab' => ["Hello\tWorld", false],
            'with_newline' => ["Hello\nWorld", false],
            'empty_string' => ['', true],
            'only_whitespace' => [' ', false],
            'non_string' => [123, false],
        ];
    }
}
