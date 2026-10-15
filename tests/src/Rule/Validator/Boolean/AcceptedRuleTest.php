<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Validator\Boolean;

use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Rule\Validator\Boolean\AcceptedRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AcceptedRule::class)]
final class AcceptedRuleTest extends TestCase
{
    #[DataProvider('validProvider')]
    public function testValid(mixed $value): void
    {
        (new AcceptedRule())->validate($value);
        $this->addToAssertionCount(1);
    }

    #[DataProvider('invalidProvider')]
    public function testInvalid(mixed $value): void
    {
        $this->expectException(ValidationException::class);
        (new AcceptedRule())->validate($value);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function validProvider(): array
    {
        return [
            'true' => [true],
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function invalidProvider(): array
    {
        return [
            'false' => [false],
            'null' => [null],
            'text true' => ['true'],
            'one' => [1],
            'text one' => ['1'],
            'empty text' => [''],
            'array' => [[true]],
        ];
    }
}
