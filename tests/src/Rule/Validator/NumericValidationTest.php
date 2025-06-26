<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule\Validator;

use Derafu\DataProcessor\Contract\ProcessorInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Processor;
use Derafu\DataProcessor\ProcessorFactory;
use Derafu\DataProcessor\Registrar\DefaultRuleRegistrar;
use Derafu\DataProcessor\Rule\Validator\Numeric\DecimalRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\DigitsRangeRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\DigitsRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\GreaterThanOrEqualRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\GreaterThanRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\IntegerRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\LessThanOrEqualRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\LessThanRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\MultipleOfRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\NumericRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\RangeRule;
use Derafu\DataProcessor\RuleParser;
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
#[CoversClass(RuleParser::class)]
#[CoversClass(NumericRule::class)]
#[CoversClass(IntegerRule::class)]
#[CoversClass(DecimalRule::class)]
#[CoversClass(DigitsRule::class)]
#[CoversClass(DigitsRangeRule::class)]
#[CoversClass(MultipleOfRule::class)]
#[CoversClass(RangeRule::class)]
#[CoversClass(GreaterThanRule::class)]
#[CoversClass(GreaterThanOrEqualRule::class)]
#[CoversClass(LessThanRule::class)]
#[CoversClass(LessThanOrEqualRule::class)]
final class NumericValidationTest extends TestCase
{
    private ProcessorInterface $processor;

    protected function setUp(): void
    {
        $this->processor = ProcessorFactory::create();
    }

    #[DataProvider('numericTypeValidationProvider')]
    public function testNumericTypeValidation(mixed $input, array $rules, bool $shouldPass): void
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

    public static function numericTypeValidationProvider(): array
    {
        return [
            'valid_numeric_integer' => [
                '123',
                ['numeric'],
                true,
            ],
            'valid_numeric_decimal' => [
                '123.45',
                ['numeric'],
                true,
            ],
            'valid_numeric_negative' => [
                '-123.45',
                ['numeric'],
                true,
            ],
            'invalid_numeric_letters' => [
                '123abc',
                ['numeric'],
                false,
            ],
            'valid_integer' => [
                '123',
                ['integer'],
                true,
            ],
            'invalid_integer_decimal' => [
                '123.45',
                ['integer'],
                false,
            ],
            'valid_decimal_two_places' => [
                '123.45',
                ['decimal:2'],
                true,
            ],
            'invalid_decimal_too_many_places' => [
                '123.456',
                ['decimal:2'],
                false,
            ],
            'valid_decimal_range' => [
                '123.45',
                ['decimal:1,2'],
                true,
            ],
            'invalid_decimal_range' => [
                '123.4',
                ['decimal:2,3'],
                false,
            ],
        ];
    }

    #[DataProvider('digitsValidationProvider')]
    public function testDigitsValidation(string $input, array $rules, bool $shouldPass): void
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

    public static function digitsValidationProvider(): array
    {
        return [
            'valid_digits_exact' => [
                '12345',
                ['digits:5'],
                true,
            ],
            'invalid_digits_too_short' => [
                '1234',
                ['digits:5'],
                false,
            ],
            'invalid_digits_too_long' => [
                '123456',
                ['digits:5'],
                false,
            ],
            'invalid_digits_decimal' => [
                '123.45',
                ['digits:5'],
                false,
            ],
            'valid_digits_range' => [
                '1234',
                ['digits_range:3,5'],
                true,
            ],
            'invalid_digits_range_too_short' => [
                '12',
                ['digits_range:3,5'],
                false,
            ],
            'invalid_digits_range_too_long' => [
                '123456',
                ['digits_range:3,5'],
                false,
            ],
        ];
    }

    #[DataProvider('multipleOfValidationProvider')]
    public function testMultipleOfValidation(mixed $input, array $rules, bool $shouldPass): void
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

    public static function multipleOfValidationProvider(): array
    {
        return [
            'valid_multiple_of_integer' => [
                10,
                ['multiple_of:2'],
                true,
            ],
            'invalid_multiple_of_integer' => [
                9,
                ['multiple_of:2'],
                false,
            ],
            'valid_multiple_of_decimal' => [
                1.5,
                ['multiple_of:0.5'],
                true,
            ],
            'invalid_multiple_of_decimal' => [
                1.7,
                ['multiple_of:0.5'],
                false,
            ],
            'valid_multiple_of_negative' => [
                -10,
                ['multiple_of:2'],
                true,
            ],
            'valid_multiple_of_zero' => [
                0,
                ['multiple_of:5'],
                true,
            ],
        ];
    }

    #[DataProvider('numberRangeValidationProvider')]
    public function testNumberRangeValidation(mixed $input, array $rules, bool $shouldPass): void
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

    public static function numberRangeValidationProvider(): array
    {
        return [
            'valid_range_integer' => [
                50,
                ['range:1,100'],
                true,
            ],
            'invalid_range_integer_too_small' => [
                0,
                ['range:1,100'],
                false,
            ],
            'invalid_range_integer_too_large' => [
                101,
                ['range:1,100'],
                false,
            ],
            'valid_range_decimal' => [
                50.5,
                ['range:1,100'],
                true,
            ],
            'valid_greater_than' => [
                10,
                ['gt:5'],
                true,
            ],
            'invalid_greater_than' => [
                5,
                ['gt:5'],
                false,
            ],
            'valid_greater_than_or_equal' => [
                5,
                ['gte:5'],
                true,
            ],
            'valid_less_than' => [
                4,
                ['lt:5'],
                true,
            ],
            'invalid_less_than' => [
                5,
                ['lt:5'],
                false,
            ],
            'valid_less_than_or_equal' => [
                5,
                ['lte:5'],
                true,
            ],
        ];
    }
}
