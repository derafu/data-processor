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
use Derafu\DataProcessor\Rule\Validator\String\SlugRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SlugRule::class)]
final class SlugRuleTest extends TestCase
{
    private SlugRule $rule;

    protected function setUp(): void
    {
        $this->rule = new SlugRule();
    }

    #[DataProvider('slugDataProvider')]
    public function testSlug(mixed $value, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $this->rule->validate($value);

        if ($shouldPass) {
            $this->assertTrue(true);
        }
    }

    public static function slugDataProvider(): array
    {
        return [
            'valid_simple' => ['hello-world', true],
            'valid_numbers' => ['article-123', true],
            'valid_single_word' => ['hello', true],
            'invalid_uppercase' => ['Hello-World', false],
            'invalid_spaces' => ['hello world', false],
            'invalid_special_chars' => ['hello_world', false],
            'invalid_consecutive_hyphens' => ['hello--world', false],
            'invalid_start_hyphen' => ['-hello-world', false],
            'invalid_end_hyphen' => ['hello-world-', false],
            'non_string' => [123, false],
        ];
    }
}
