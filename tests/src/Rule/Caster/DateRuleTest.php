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

    public function testTheMessageSaysWhyTheValueIsNotValid(): void
    {
        $this->assertSame(
            'Invalid date format: null is not valid.',
            $this->messageOf(null)
        );
        $this->assertSame(
            'Invalid date format: numeric is not valid.',
            $this->messageOf(123)
        );
        $this->assertSame(
            'Invalid date format: not an absolute ISO 8601 date.',
            $this->messageOf('not a date')
        );
        $this->assertSame(
            'Invalid date format: not an absolute ISO 8601 date.',
            $this->messageOf('tomorrow')
        );
        $this->assertStringStartsWith('Invalid date format: ', $this->messageOf('2024-01-15T10:30+25:00'));
        // What is neither text nor a date says its type, not the error of PHP.
        $this->assertSame('Invalid date format: array is not valid.', $this->messageOf([2024, 1, 15]));
        $this->assertSame('Invalid date format: bool is not valid.', $this->messageOf(true));
        $this->assertSame('Invalid date format: stdClass is not valid.', $this->messageOf(new \stdClass()));
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

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function dateCastDataProvider(): array
    {
        return [
            'date_string' => ['2024-01-15', '2024-01-15'],
            'datetime_string' => ['2024-01-15 15:30:00', '2024-01-15'],
            'carbon_instance' => [Carbon::create(2024, 1, 15, 15, 30, 0), '2024-01-15'],
            'datetime_immutable' => [new DateTimeImmutable('2024-01-15 15:30:00'), '2024-01-15'],
            'iso8601_with_offset' => ['2024-01-15T23:30:00-03:00', '2024-01-15'],
        ];
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function invalidDateCastDataProvider(): array
    {
        return [
            'invalid_date' => ['not a date'],
            'relative_text' => ['tomorrow'],
            'relative_offset' => ['+1 week'],
            'now' => ['now'],
            'human_date' => ['15 January 2024'],
            'ambiguous_format' => ['01/02/2024'],
            'day_first_format' => ['15-01-2024'],
            'date_that_does_not_exist' => ['2024-02-30'],
            'offset_that_does_not_exist' => ['2024-01-15T10:30+25:00'],
            'null' => [null],
            'number' => [123],
            'array' => [[2024,1,15]],
        ];
    }
}
