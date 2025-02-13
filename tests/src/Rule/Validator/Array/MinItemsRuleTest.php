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
use Derafu\DataProcessor\Rule\Validator\Array\MinItemsRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MinItemsRule::class)]
final class MinItemsRuleTest extends TestCase
{
    private MinItemsRule $rule;

    protected function setUp(): void
    {
        $this->rule = new MinItemsRule();
    }

    #[DataProvider('minItemsDataProvider')]
    public function testMinItems(mixed $value, array $parameters, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $this->rule->validate($value, $parameters);

        if ($shouldPass) {
            $this->assertTrue(true);
        }
    }

    public static function minItemsDataProvider(): array
    {
        return [
            'enough_items' => [[1, 2, 3], ['2'], true],
            'exact_items' => [[1, 2], ['2'], true],
            'too_few_items' => [[1], ['2'], false],
            'empty_array' => [[], ['1'], false],
            'non_array' => ['string', ['1'], false],
            'no_parameter' => [[1, 2, 3], [], false],
        ];
    }
}
