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
use Derafu\DataProcessor\Rule\Caster\TimestampRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimestampRule::class)]
final class TimestampRuleTest extends TestCase
{
    private TimestampRule $rule;

    protected function setUp(): void
    {
        $this->rule = new TimestampRule();
        Carbon::setTestNow(Carbon::create(2024, 1, 1, 12, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    #[DataProvider('timestampCastDataProvider')]
    public function testTimestampCast(mixed $input, int $expected): void
    {
        $result = $this->rule->cast($input);
        $this->assertSame($expected, $result);
    }

    #[DataProvider('invalidTimestampCastDataProvider')]
    public function testInvalidTimestampCast(mixed $input): void
    {
        $this->expectException(CastingException::class);
        $this->rule->cast($input);
    }

    public static function timestampCastDataProvider(): array
    {
        $timestamp = Carbon::create(2024, 1, 15, 15, 30, 0)->timestamp;

        return [
            'numeric_timestamp' => [$timestamp, $timestamp],
            'string_timestamp' => [(string)$timestamp, $timestamp],
            'datetime_string' => ['2024-01-15 15:30:00', $timestamp],
            'date_string' => ['2024-01-15', Carbon::create(2024, 1, 15, 0, 0, 0)->timestamp],
            'carbon_instance' => [Carbon::create(2024, 1, 15, 15, 30, 0), $timestamp],
            'relative_time' => ['tomorrow', Carbon::create(2024, 1, 2, 0, 0, 0)->timestamp],
        ];
    }

    public static function invalidTimestampCastDataProvider(): array
    {
        return [
            'invalid_datetime' => ['not a timestamp'],
            'null' => [null],
            'array' => [[2024,1,15]],
            'boolean' => [true],
            'invalid_format' => ['2024-13-45'],
        ];
    }
}
