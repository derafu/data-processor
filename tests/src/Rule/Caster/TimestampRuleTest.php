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
use DateTimeImmutable;
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

    public function testTheMessageSaysWhyTheValueIsNotValid(): void
    {
        $this->assertSame('Invalid timestamp format: null is not valid.', $this->messageOf(null));
        $this->assertStringStartsWith('Invalid timestamp format: ', $this->messageOf('not a date'));
        // What is neither text nor a date says its type, not the error of PHP.
        $this->assertSame('Invalid timestamp format: array is not valid.', $this->messageOf([2024, 1, 15]));
        $this->assertSame('Invalid timestamp format: bool is not valid.', $this->messageOf(true));
        $this->assertSame('Invalid timestamp format: stdClass is not valid.', $this->messageOf(new \stdClass()));
    }

    private function messageOf(mixed $input): string
    {
        try {
            $this->rule->cast($input);
        } catch (CastingException $e) {
            return $e->getMessage();
        }

        $this->fail('It should not be possible to cast this value.');
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
            'datetime_immutable' => [new DateTimeImmutable('2024-01-15 15:30:00'), $timestamp],
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
