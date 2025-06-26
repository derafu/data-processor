<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Caster;

use Carbon\Carbon;
use Derafu\DataProcessor\Exception\CastingException;
use Derafu\DataProcessor\Rule\Caster\DateTimeRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateTimeRule::class)]
final class DateTimeRuleTest extends TestCase
{
    private DateTimeRule $rule;

    protected function setUp(): void
    {
        $this->rule = new DateTimeRule();
        Carbon::setTestNow(Carbon::create(2024, 1, 1, 12, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    #[DataProvider('dateTimeCastDataProvider')]
    public function testDateTimeCast(mixed $input, string $expectedDateTime): void
    {
        $result = $this->rule->cast($input);
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertSame($expectedDateTime, $result->toDateTimeString());
    }

    #[DataProvider('invalidDateTimeCastDataProvider')]
    public function testInvalidDateTimeCast(mixed $input): void
    {
        $this->expectException(CastingException::class);
        $this->rule->cast($input);
    }

    public static function dateTimeCastDataProvider(): array
    {
        return [
            'datetime_string' => ['2024-01-15 15:30:00', '2024-01-15 15:30:00'],
            'date_string' => ['2024-01-15', '2024-01-15 00:00:00'],
            'carbon_instance' => [
                Carbon::create(2024, 1, 15, 15, 30, 0),
                '2024-01-15 15:30:00',
            ],
            'human_datetime' => ['15 January 2024 15:30', '2024-01-15 15:30:00'],
            'relative_time' => ['tomorrow', '2024-01-02 00:00:00'],
            'iso8601' => ['2024-01-15T15:30:00+00:00', '2024-01-15 15:30:00'],
        ];
    }

    public static function invalidDateTimeCastDataProvider(): array
    {
        return [
            'invalid_datetime' => ['not a datetime'],
            'null' => [null],
            'number' => [123],
            'array' => [[2024,1,15,15,30,0]],
            'invalid_format' => ['2024-13-45 25:70:99'],
        ];
    }
}
