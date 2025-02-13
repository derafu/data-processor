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
use Derafu\DataProcessor\Rule\Validator\Array\UniqueRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UniqueRule::class)]
final class UniqueRuleTest extends TestCase
{
    private UniqueRule $rule;

    protected function setUp(): void
    {
        $this->rule = new UniqueRule();
    }

    #[DataProvider('uniqueDataProvider')]
    public function testUnique(mixed $value, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $this->rule->validate($value);

        if ($shouldPass) {
            $this->assertTrue(true);
        }
    }

    public static function uniqueDataProvider(): array
    {
        return [
            'unique_numbers' => [[1, 2, 3, 4, 5], true],
            'duplicate_numbers' => [[1, 2, 2, 3], false],
            'unique_strings' => [['a', 'b', 'c'], true],
            'duplicate_strings' => [['a', 'b', 'b'], false],
            //'unique_mixed' => [[1, 'a', true], true],
            'duplicate_mixed' => [[1, 'a', 1], false],
            'empty_array' => [[], true],
            'non_array' => ['string', false],
            'unique_objects' => [
                [
                    (object)['id' => 1],
                    (object)['id' => 2],
                ],
                true,
            ],
            'duplicate_objects' => [
                [
                    (object)['id' => 1],
                    (object)['id' => 1],
                ],
                false,
            ],
        ];
    }
}
