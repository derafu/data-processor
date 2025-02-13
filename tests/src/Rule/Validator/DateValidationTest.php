<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Validator;

use Carbon\Carbon;
use Derafu\DataProcessor\Contract\ProcessorInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Processor;
use Derafu\DataProcessor\ProcessorFactory;
use Derafu\DataProcessor\Registrar\DefaultRuleRegistrar;
use Derafu\DataProcessor\Rule\Validator\Date\AfterOrEqualRule;
use Derafu\DataProcessor\Rule\Validator\Date\AfterRule;
use Derafu\DataProcessor\Rule\Validator\Date\BeforeOrEqualRule;
use Derafu\DataProcessor\Rule\Validator\Date\BeforeRule;
use Derafu\DataProcessor\Rule\Validator\Date\DateEqualsRule;
use Derafu\DataProcessor\Rule\Validator\Date\DateFormatRule;
use Derafu\DataProcessor\Rule\Validator\Date\WeekdayRule;
use Derafu\DataProcessor\Rule\Validator\Date\WeekendRule;
use Derafu\DataProcessor\RuleRegistry;
use Derafu\DataProcessor\RuleResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Processor::class)]
#[CoversClass(DefaultRuleRegistrar::class)]
#[CoversClass(ProcessorFactory::class)]
#[CoversClass(RuleRegistry::class)]
#[CoversClass(RuleResolver::class)]
#[CoversClass(DateFormatRule::class)]
#[CoversClass(AfterOrEqualRule::class)]
#[CoversClass(BeforeOrEqualRule::class)]
#[CoversClass(DateEqualsRule::class)]
#[CoversClass(AfterRule::class)]
#[CoversClass(BeforeRule::class)]
#[CoversClass(WeekdayRule::class)]
#[CoversClass(WeekendRule::class)]
final class DateValidationTest extends TestCase
{
    private ProcessorInterface $processor;

    private $now;

    protected function setUp(): void
    {
        $this->processor = ProcessorFactory::create();
        // Fijar una fecha "actual" para los tests.
        $this->now = Carbon::create(2024, 2, 15, 12, 0, 0);
        Carbon::setTestNow($this->now);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
    }

    #[DataProvider('dateFormatValidationProvider')]
    public function testDateFormatValidation(string $input, array $rules, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $result = $this->processor->process($input, [
            'validate' => $rules,
        ]);

        if ($shouldPass) {
            $this->assertSame($input, $result);
        }
    }

    public static function dateFormatValidationProvider(): array
    {
        return [
            'valid_date_ymd' => [
                '2024-02-15',
                ['date_format:Y-m-d'],
                true,
            ],
            'invalid_date_ymd' => [
                '15-02-2024',
                ['date_format:Y-m-d'],
                false,
            ],
            'valid_date_dmy' => [
                '15/02/2024',
                ['date_format:d/m/Y'],
                true,
            ],
            'invalid_date_dmy' => [
                '2024/02/15',
                ['date_format:d/m/Y'],
                false,
            ],
            'valid_datetime' => [
                '2024-02-15 12:30:45',
                ['date_format:Y-m-d H:i:s'],
                true,
            ],
            'invalid_datetime' => [
                '2024-02-15 25:00:00',
                ['date_format:Y-m-d H:i:s'],
                false,
            ],
            'invalid_custom_format_1' => [
                'Feb 15, 2024',
                ['date_format:M d, Y'],
                false,
            ],
            'invalid_custom_format_2' => [
                'February 15th, 2024',
                ['date_format:M d, Y'],
                false,
            ],
        ];
    }

    #[DataProvider('dateComparisonProvider')]
    public function testDateComparison(string $input, array $rules, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $result = $this->processor->process($input, [
            'validate' => $rules,
        ]);

        if ($shouldPass) {
            $this->assertSame($input, $result);
        }
    }

    public static function dateComparisonProvider(): array
    {
        return [
            'valid_after' => [
                '2024-02-16',
                ['after:2024-02-15'],
                true,
            ],
            'invalid_after' => [
                '2024-02-14',
                ['after:2024-02-15'],
                false,
            ],
            'valid_after_or_equal' => [
                '2024-02-15',
                ['after_or_equal:2024-02-15'],
                true,
            ],
            'invalid_after_or_equal' => [
                '2024-02-14',
                ['after_or_equal:2024-02-15'],
                false,
            ],
            'valid_before' => [
                '2024-02-14',
                ['before:2024-02-15'],
                true,
            ],
            'invalid_before' => [
                '2024-02-16',
                ['before:2024-02-15'],
                false,
            ],
            'valid_before_or_equal' => [
                '2024-02-15',
                ['before_or_equal:2024-02-15'],
                true,
            ],
            'invalid_before_or_equal' => [
                '2024-02-16',
                ['before_or_equal:2024-02-15'],
                false,
            ],
            'valid_equals' => [
                '2024-02-15',
                ['date_equals:2024-02-15'],
                true,
            ],
            'invalid_equals' => [
                '2024-02-16',
                ['date_equals:2024-02-15'],
                false,
            ],
        ];
    }

    #[DataProvider('relativeDateValidationProvider')]
    public function testRelativeDateValidation(string $input, array $rules, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $result = $this->processor->process($input, [
            'validate' => $rules,
        ]);

        if ($shouldPass) {
            $this->assertSame($input, $result);
        }
    }

    public static function relativeDateValidationProvider(): array
    {
        return [
            'valid_after_now' => [
                '2024-02-16',
                ['after:now'],
                true,
            ],
            'invalid_after_now' => [
                '2024-02-14',
                ['after:now'],
                false,
            ],
            'valid_before_now' => [
                '2024-02-14',
                ['before:now'],
                true,
            ],
            'invalid_before_now' => [
                '2024-02-16',
                ['before:now'],
                false,
            ],
            'valid_after_relative' => [
                '2024-02-20',
                ['after:2024-02-19'],
                true,
            ],
            'invalid_after_relative' => [
                '2024-02-18',
                ['after:2024-02-19'],
                false,
            ],
            'valid_before_relative' => [
                '2024-02-10',
                ['before:2024-02-11'],
                true,
            ],
            'invalid_before_relative' => [
                '2024-02-12',
                ['before:2024-02-11'],
                false,
            ],
        ];
    }

    #[DataProvider('weekendWeekdayValidationProvider')]
    public function testWeekendWeekdayValidation(string $input, array $rules, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $result = $this->processor->process($input, [
            'validate' => $rules,
        ]);

        if ($shouldPass) {
            $this->assertSame($input, $result);
        }
    }

    public static function weekendWeekdayValidationProvider(): array
    {
        return [
            'valid_weekday' => [
                '2024-02-15', // Thursday.
                ['weekday'],
                true,
            ],
            'invalid_weekday' => [
                '2024-02-17', // Saturday.
                ['weekday'],
                false,
            ],
            'valid_weekend' => [
                '2024-02-17', // Saturday.
                ['weekend'],
                true,
            ],
            'invalid_weekend' => [
                '2024-02-15', // Thursday.
                ['weekend'],
                false,
            ],
        ];
    }
}
