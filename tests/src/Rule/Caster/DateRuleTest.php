<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Caster;

use Carbon\Carbon;
use Derafu\DataProcessor\Exception\CastingException;
use Derafu\DataProcessor\Rule\Caster\DateRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateRule::class)]
final class DateRuleTest extends TestCase
{
    private DateRule $rule;

    protected function setUp(): void
    {
        $this->rule = new DateRule();
        Carbon::setTestNow(Carbon::create(2024, 1, 1, 12, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    #[DataProvider('dateCastDataProvider')]
    public function testDateCast(mixed $input, string $expectedDate): void
    {
        $result = $this->rule->cast($input);
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertTrue($result->isStartOfDay());
        $this->assertSame($expectedDate, $result->toDateString());
    }

    #[DataProvider('invalidDateCastDataProvider')]
    public function testInvalidDateCast(mixed $input): void
    {
        $this->expectException(CastingException::class);
        $this->rule->cast($input);
    }

    public static function dateCastDataProvider(): array
    {
        return [
            'date_string' => ['2024-01-15', '2024-01-15'],
            'datetime_string' => ['2024-01-15 15:30:00', '2024-01-15'],
            'carbon_instance' => [Carbon::create(2024, 1, 15, 15, 30, 0), '2024-01-15'],
            'human_date' => ['15 January 2024', '2024-01-15'],
        ];
    }

    public static function invalidDateCastDataProvider(): array
    {
        return [
            'invalid_date' => ['not a date'],
            'null' => [null],
            'number' => [123],
            'array' => [[2024,1,15]],
        ];
    }
}
