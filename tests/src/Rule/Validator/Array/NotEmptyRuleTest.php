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
use Derafu\DataProcessor\Rule\Validator\Array\NotEmptyRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NotEmptyRule::class)]
final class NotEmptyRuleTest extends TestCase
{
    private NotEmptyRule $rule;

    protected function setUp(): void
    {
        $this->rule = new NotEmptyRule();
    }

    #[DataProvider('notEmptyDataProvider')]
    public function testNotEmpty(mixed $value, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $this->rule->validate($value);

        if ($shouldPass) {
            $this->assertTrue(true);
        }
    }

    public static function notEmptyDataProvider(): array
    {
        return [
            'non_empty_array' => [[1, 2, 3], true],
            'single_item' => [[1], true],
            'empty_array' => [[], false],
            'non_array' => ['string', false],
        ];
    }
}
