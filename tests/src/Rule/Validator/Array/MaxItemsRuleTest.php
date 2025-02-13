<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Validator\Array;

use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Rule\Validator\Array\MaxItemsRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MaxItemsRule::class)]
final class MaxItemsRuleTest extends TestCase
{
    private MaxItemsRule $rule;

    protected function setUp(): void
    {
        $this->rule = new MaxItemsRule();
    }

    #[DataProvider('maxItemsDataProvider')]
    public function testMaxItems(mixed $value, array $parameters, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $this->rule->validate($value, $parameters);

        if ($shouldPass) {
            $this->assertTrue(true);
        }
    }

    public static function maxItemsDataProvider(): array
    {
        return [
            'within_limit' => [[1, 2], ['3'], true],
            'exact_limit' => [[1, 2, 3], ['3'], true],
            'too_many_items' => [[1, 2, 3, 4], ['3'], false],
            'empty_array' => [[], ['3'], true],
            'non_array' => ['string', ['3'], false],
            'no_parameter' => [[1, 2, 3], [], false],
        ];
    }
}
