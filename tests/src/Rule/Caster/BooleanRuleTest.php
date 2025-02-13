<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Caster;

use Derafu\DataProcessor\Rule\Caster\BooleanRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(BooleanRule::class)]
final class BooleanRuleTest extends TestCase
{
    private BooleanRule $rule;

    protected function setUp(): void
    {
        $this->rule = new BooleanRule();
    }

    #[DataProvider('booleanCastDataProvider')]
    public function testBooleanCast(mixed $input, bool $expected): void
    {
        $result = $this->rule->cast($input);
        $this->assertSame($expected, $result);
    }

    public static function booleanCastDataProvider(): array
    {
        return [
            'true_string' => ['true', true],
            'false_string' => ['false', false],
            'yes_string' => ['yes', true],
            'no_string' => ['no', false],
            'on_string' => ['on', true],
            'off_string' => ['off', false],
            '1_string' => ['1', true],
            '0_string' => ['0', false],
            'true_value' => [true, true],
            'false_value' => [false, false],
            'empty_string' => ['', false],
            'null' => [null, false],
            'integer_1' => [1, true],
            'integer_0' => [0, false],
        ];
    }
}
