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
use Derafu\DataProcessor\Rule\Validator\String\LengthRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(LengthRule::class)]
final class LengthRuleTest extends TestCase
{
    private LengthRule $rule;

    protected function setUp(): void
    {
        $this->rule = new LengthRule();
    }

    #[DataProvider('lengthDataProvider')]
    public function testLength(mixed $value, array $parameters, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $this->rule->validate($value, $parameters);

        if ($shouldPass) {
            $this->assertTrue(true);
        }
    }

    public static function lengthDataProvider(): array
    {
        return [
            'exact_length' => ['hello', ['5'], true],
            'too_short' => ['hi', ['5'], false],
            'too_long' => ['hello world', ['5'], false],
            'unicode_exact' => ['héllo', ['5'], true],
            'unicode_too_short' => ['hé', ['5'], false],
            'non_string' => [123, ['3'], false],
            'missing_parameter' => ['hello', [], false],
            'zero_length_treated_as_missing' => ['', ['0'], false],
        ];
    }
}
