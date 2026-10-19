<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Rule;

use Carbon\Carbon;
use Derafu\DataProcessor\Contract\SanitizerRuleInterface;
use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\CastingException;
use Derafu\DataProcessor\Exception\SanitizationException;
use Derafu\DataProcessor\Exception\TransformationException;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Rule\Caster\StringRule;
use Derafu\DataProcessor\Rule\Sanitizer\RegexKeepRule;
use Derafu\DataProcessor\Rule\Sanitizer\RegexRemoveRule;
use Derafu\DataProcessor\Rule\Sanitizer\RemoveCharsRule;
use Derafu\DataProcessor\Rule\Sanitizer\RemovePrefixRule;
use Derafu\DataProcessor\Rule\Sanitizer\RemoveSuffixRule;
use Derafu\DataProcessor\Rule\Sanitizer\SubStringRule;
use Derafu\DataProcessor\Rule\Transformer\Json\JsonEncodeRule;
use Derafu\DataProcessor\Rule\Transformer\Json\JsonToArrayRule;
use Derafu\DataProcessor\Rule\Transformer\String\SlugRule;
use Derafu\DataProcessor\Rule\Transformer\String\TransliterateRule;
use Derafu\DataProcessor\Rule\Validator\Array\MaxItemsRule;
use Derafu\DataProcessor\Rule\Validator\Array\MinItemsRule;
use Derafu\DataProcessor\Rule\Validator\Date\AfterOrEqualRule;
use Derafu\DataProcessor\Rule\Validator\Date\AfterRule;
use Derafu\DataProcessor\Rule\Validator\Date\BeforeOrEqualRule;
use Derafu\DataProcessor\Rule\Validator\Date\BeforeRule;
use Derafu\DataProcessor\Rule\Validator\Date\BetweenRule;
use Derafu\DataProcessor\Rule\Validator\Date\DateEqualsRule;
use Derafu\DataProcessor\Rule\Validator\Date\DateFormatRule;
use Derafu\DataProcessor\Rule\Validator\Date\DatetimeLocalRule;
use Derafu\DataProcessor\Rule\Validator\Date\MonthRule;
use Derafu\DataProcessor\Rule\Validator\Date\TimeRule;
use Derafu\DataProcessor\Rule\Validator\Date\WeekRule;
use Derafu\DataProcessor\Rule\Validator\Financial\BicRule;
use Derafu\DataProcessor\Rule\Validator\Financial\CardNumberRule;
use Derafu\DataProcessor\Rule\Validator\Financial\IbanRule;
use Derafu\DataProcessor\Rule\Validator\I18n\CountryRule;
use Derafu\DataProcessor\Rule\Validator\I18n\CurrencyRule;
use Derafu\DataProcessor\Rule\Validator\I18n\LanguageRule;
use Derafu\DataProcessor\Rule\Validator\I18n\LocaleRule;
use Derafu\DataProcessor\Rule\Validator\I18n\TimezoneRule;
use Derafu\DataProcessor\Rule\Validator\Id\UuidRule;
use Derafu\DataProcessor\Rule\Validator\Internet\EmailRule;
use Derafu\DataProcessor\Rule\Validator\Internet\HostnameRule;
use Derafu\DataProcessor\Rule\Validator\Internet\HostRule;
use Derafu\DataProcessor\Rule\Validator\Internet\IpRule;
use Derafu\DataProcessor\Rule\Validator\Internet\UrlRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\DecimalRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\DigitsRangeRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\DigitsRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\GreaterThanOrEqualRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\GreaterThanRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\IntegerRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\LessThanOrEqualRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\LessThanRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\MultipleOfRule;
use Derafu\DataProcessor\Rule\Validator\Numeric\RangeRule;
use Derafu\DataProcessor\Rule\Validator\String\AlphaDashRule;
use Derafu\DataProcessor\Rule\Validator\String\AlphaNumRule;
use Derafu\DataProcessor\Rule\Validator\String\AlphaRule;
use Derafu\DataProcessor\Rule\Validator\String\ColorRule;
use Derafu\DataProcessor\Rule\Validator\String\EndsWithRule;
use Derafu\DataProcessor\Rule\Validator\String\LengthRule;
use Derafu\DataProcessor\Rule\Validator\String\MaxLengthRule;
use Derafu\DataProcessor\Rule\Validator\String\MinLengthRule;
use Derafu\DataProcessor\Rule\Validator\String\NotRegexRule;
use Derafu\DataProcessor\Rule\Validator\String\RegexRule;
use Derafu\DataProcessor\Rule\Validator\String\StartsWithRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Stringable;

/**
 * What the rules do when what they receive is not what they need: a value of
 * another type, a parameter that is missing or one that is not valid. Each one
 * says it with its own error.
 */
#[CoversClass(StringRule::class)]
#[CoversClass(RegexKeepRule::class)]
#[CoversClass(RegexRemoveRule::class)]
#[CoversClass(RemoveCharsRule::class)]
#[CoversClass(RemovePrefixRule::class)]
#[CoversClass(RemoveSuffixRule::class)]
#[CoversClass(SubStringRule::class)]
#[CoversClass(JsonEncodeRule::class)]
#[CoversClass(JsonToArrayRule::class)]
#[CoversClass(SlugRule::class)]
#[CoversClass(TransliterateRule::class)]
#[CoversClass(AfterOrEqualRule::class)]
#[CoversClass(AfterRule::class)]
#[CoversClass(BeforeOrEqualRule::class)]
#[CoversClass(BeforeRule::class)]
#[CoversClass(BetweenRule::class)]
#[CoversClass(DateEqualsRule::class)]
#[CoversClass(DateFormatRule::class)]
#[CoversClass(BicRule::class)]
#[CoversClass(CardNumberRule::class)]
#[CoversClass(IbanRule::class)]
#[CoversClass(CountryRule::class)]
#[CoversClass(CurrencyRule::class)]
#[CoversClass(LanguageRule::class)]
#[CoversClass(LocaleRule::class)]
#[CoversClass(TimezoneRule::class)]
#[CoversClass(UuidRule::class)]
#[CoversClass(EmailRule::class)]
#[CoversClass(HostnameRule::class)]
#[CoversClass(HostRule::class)]
#[CoversClass(WeekRule::class)]
#[CoversClass(TimeRule::class)]
#[CoversClass(MonthRule::class)]
#[CoversClass(DatetimeLocalRule::class)]
#[CoversClass(ColorRule::class)]
#[CoversClass(IpRule::class)]
#[CoversClass(UrlRule::class)]
#[CoversClass(DecimalRule::class)]
#[CoversClass(DigitsRangeRule::class)]
#[CoversClass(DigitsRule::class)]
#[CoversClass(GreaterThanOrEqualRule::class)]
#[CoversClass(GreaterThanRule::class)]
#[CoversClass(IntegerRule::class)]
#[CoversClass(LessThanOrEqualRule::class)]
#[CoversClass(LessThanRule::class)]
#[CoversClass(MultipleOfRule::class)]
#[CoversClass(RangeRule::class)]
#[CoversClass(AlphaDashRule::class)]
#[CoversClass(AlphaNumRule::class)]
#[CoversClass(AlphaRule::class)]
#[CoversClass(EndsWithRule::class)]
#[CoversClass(NotRegexRule::class)]
#[CoversClass(RegexRule::class)]
#[CoversClass(StartsWithRule::class)]
#[CoversClass(LengthRule::class)]
#[CoversClass(MaxLengthRule::class)]
#[CoversClass(MinLengthRule::class)]
#[CoversClass(MaxItemsRule::class)]
#[CoversClass(MinItemsRule::class)]
final class RuleGuardsTest extends TestCase
{
    /**
     * @return array<string, array{ValidatorRuleInterface}>
     */
    public static function stringRulesProvider(): array
    {
        return [
            'BicRule' => [new BicRule()],
            'CardNumberRule' => [new CardNumberRule()],
            'IbanRule' => [new IbanRule()],
            'CountryRule' => [new CountryRule()],
            'CurrencyRule' => [new CurrencyRule()],
            'LanguageRule' => [new LanguageRule()],
            'LocaleRule' => [new LocaleRule()],
            'TimezoneRule' => [new TimezoneRule()],
            'UuidRule' => [new UuidRule()],
            'EmailRule' => [new EmailRule()],
            'HostnameRule' => [new HostnameRule()],
            'HostRule' => [new HostRule()],
            'ColorRule' => [new ColorRule()],
            'DatetimeLocalRule' => [new DatetimeLocalRule()],
            'MonthRule' => [new MonthRule()],
            'TimeRule' => [new TimeRule()],
            'WeekRule' => [new WeekRule()],
            'IpRule' => [new IpRule()],
            'UrlRule' => [new UrlRule()],
            'AlphaDashRule' => [new AlphaDashRule()],
            'AlphaNumRule' => [new AlphaNumRule()],
            'AlphaRule' => [new AlphaRule()],
            'EndsWithRule' => [new EndsWithRule()],
            'NotRegexRule' => [new NotRegexRule()],
            'RegexRule' => [new RegexRule()],
            'StartsWithRule' => [new StartsWithRule()],
            'DateFormatRule' => [new DateFormatRule()],
        ];
    }

    /**
     * @return array<string, array{ValidatorRuleInterface}>
     */
    public static function numericRulesProvider(): array
    {
        return [
            'DecimalRule' => [new DecimalRule()],
            'GreaterThanOrEqualRule' => [new GreaterThanOrEqualRule()],
            'GreaterThanRule' => [new GreaterThanRule()],
            'IntegerRule' => [new IntegerRule()],
            'LessThanOrEqualRule' => [new LessThanOrEqualRule()],
            'LessThanRule' => [new LessThanRule()],
            'MultipleOfRule' => [new MultipleOfRule()],
            'RangeRule' => [new RangeRule()],
        ];
    }

    /**
     * @return array<string, array{ValidatorRuleInterface, string, string}>
     */
    public static function missingParametersProvider(): array
    {
        return [
            'DecimalRule' => [new DecimalRule(), '1.5', 'Decimal places must be specified.'],
            'DigitsRangeRule' => [new DigitsRangeRule(), '5', 'Min and max digits must be specified.'],
            'DigitsRule' => [new DigitsRule(), '5', 'Number of digits must be specified.'],
            'GreaterThanOrEqualRule' => [new GreaterThanOrEqualRule(), '5', 'Comparison value must be specified.'],
            'GreaterThanRule' => [new GreaterThanRule(), '5', 'Comparison value must be specified.'],
            'LessThanOrEqualRule' => [new LessThanOrEqualRule(), '5', 'Comparison value must be specified.'],
            'LessThanRule' => [new LessThanRule(), '5', 'Comparison value must be specified.'],
            'MultipleOfRule' => [new MultipleOfRule(), '5', 'Multiple value must be specified.'],
            'RangeRule' => [new RangeRule(), '5', 'Min and max values must be specified.'],
            'EndsWithRule' => [new EndsWithRule(), 'abc', 'End string must be specified.'],
            'StartsWithRule' => [new StartsWithRule(), 'abc', 'Start string must be specified.'],
            'NotRegexRule' => [new NotRegexRule(), 'abc', 'Regular expression pattern must be specified.'],
            'RegexRule' => [new RegexRule(), 'abc', 'Regular expression pattern must be specified.'],
            'DateFormatRule' => [new DateFormatRule(), '2024-01-01', 'Date format must be specified.'],
            'AfterRule' => [new AfterRule(), '2024-01-01', 'Comparison date must be specified.'],
            'AfterOrEqualRule' => [new AfterOrEqualRule(), '2024-01-01', 'Comparison date must be specified.'],
            'BeforeRule' => [new BeforeRule(), '2024-01-01', 'Comparison date must be specified.'],
            'BeforeOrEqualRule' => [new BeforeOrEqualRule(), '2024-01-01', 'Comparison date must be specified.'],
            'DateEqualsRule' => [new DateEqualsRule(), '2024-01-01', 'Comparison date must be specified.'],
        ];
    }

    /**
     * @return array<string, array{SanitizerRuleInterface, string}>
     */
    public static function sanitizersProvider(): array
    {
        return [
            'RegexKeepRule' => [new RegexKeepRule(), 'Pattern parameter must be specified.'],
            'RegexRemoveRule' => [new RegexRemoveRule(), 'Pattern parameter must be specified.'],
            'RemoveCharsRule' => [new RemoveCharsRule(), 'Chars parameter must be specified.'],
            'RemovePrefixRule' => [new RemovePrefixRule(), 'Prefix parameter must be specified.'],
            'RemoveSuffixRule' => [new RemoveSuffixRule(), 'Suffix parameter must be specified.'],
            'SubStringRule' => [new SubStringRule(), 'Length parameter must be specified.'],
        ];
    }

    /**
     * @param ValidatorRuleInterface $rule
     */
    #[Test]
    #[DataProvider('stringRulesProvider')]
    public function shouldRejectAValueThatIsNotAStringWhereAStringIsNeeded(ValidatorRuleInterface $rule): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Value must be a string.');

        $rule->validate(5, ['x']);
    }

    /**
     * @param ValidatorRuleInterface $rule
     */
    #[Test]
    #[DataProvider('numericRulesProvider')]
    public function shouldRejectAValueThatIsNotNumericWhereANumberIsNeeded(ValidatorRuleInterface $rule): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Value must be numeric.');

        $rule->validate('abc', ['1']);
    }

    /**
     * @param ValidatorRuleInterface $rule
     */
    #[Test]
    #[DataProvider('missingParametersProvider')]
    public function shouldSayWhichParameterIsMissing(ValidatorRuleInterface $rule, string $value, string $message): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($message);

        $rule->validate($value, []);
    }

    /**
     * @param SanitizerRuleInterface $rule
     */
    #[Test]
    #[DataProvider('sanitizersProvider')]
    public function shouldSayWhichParameterOfASanitizerIsMissing(SanitizerRuleInterface $rule, string $message): void
    {
        $this->expectException(SanitizationException::class);
        $this->expectExceptionMessage($message);

        $rule->sanitize('abc', []);
    }

    #[Test]
    public function shouldRejectAnInvalidIpVersionInTheHostRule(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid parameter: v5');

        (new HostRule())->validate('example.com', ['v5']);
    }

    #[Test]
    public function shouldRejectAHostnameThatIsTooLong(): void
    {
        $rule = new HostnameRule();

        try {
            $rule->validate(str_repeat('a.', 127) . 'a');
            $this->fail('A hostname of more than 253 characters must be rejected.');
        } catch (ValidationException $e) {
            $this->assertSame('Hostname too long.', $e->getMessage());
        }

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Hostname part too long.');

        $rule->validate(str_repeat('a', 64) . '.com');
    }

    #[Test]
    public function shouldRejectAMultipleOfZero(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Multiple cannot be zero.');

        (new MultipleOfRule())->validate('5', ['0']);
    }

    #[Test]
    public function shouldRejectAPatternThatIsNotValid(): void
    {
        foreach ([new RegexRule(), new NotRegexRule()] as $rule) {
            try {
                $rule->validate('abc', ['/(/']);
                $this->fail('A pattern that is not valid must be rejected.');
            } catch (ValidationException $e) {
                $this->assertStringStartsWith('Invalid regular expression pattern: ', $e->getMessage());
                $this->assertStringContainsString('preg_match()', $e->getMessage());
            }
        }
    }

    #[Test]
    public function shouldRejectTheValuesOfTheFinancialRulesThatAreNotValid(): void
    {
        $cases = [
            [new BicRule(), 'DEUTZZFF', 'Invalid country code in BIC.'],
            [new CardNumberRule(), '1234', 'Invalid card number length.'],
            [new CardNumberRule(), 'abc4532015112830366xyz', 'Invalid card number format.'],
            [new IbanRule(), '1234', 'Invalid IBAN format.'],
            [new IbanRule(), 'XX00', 'Invalid country code in IBAN.'],
            [new IbanRule(), 'XX97123456789012345678', 'Invalid country code in IBAN.'],
            [new IbanRule(), 'DE813704004405320130000', 'Invalid IBAN length.'],
            [new IbanRule(), 'DE89370400440532013', 'Invalid IBAN length.'],
            [new IbanRule(), 'DE00370400440532013000', 'Invalid IBAN checksum.'],
        ];

        foreach ($cases as [$rule, $value, $message]) {
            try {
                $rule->validate($value);
                $this->fail('A value that is not valid must be rejected.');
            } catch (ValidationException $e) {
                $this->assertSame($message, $e->getMessage());
            }
        }
    }

    #[Test]
    public function shouldRejectAComparisonDateThatIsNotADate(): void
    {
        $rules = [new AfterRule(), new AfterOrEqualRule(), new BeforeRule(), new BeforeOrEqualRule(), new DateEqualsRule()];

        foreach ($rules as $rule) {
            try {
                $rule->validate('2024-01-01', ['not a date']);
                $this->fail('A comparison date that is not a date must be rejected.');
            } catch (ValidationException $e) {
                $this->assertSame('Invalid parameter: not a date', $e->getMessage());
            }
        }

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid parameter: not a date');

        (new BetweenRule())->validate('2024-06-01', ['not a date', '2024-12-31']);
    }

    #[Test]
    public function shouldRejectAValueThatIsNotMadeOfDigitsWhereDigitsAreNeeded(): void
    {
        $rules = [new DigitsRule(), new DigitsRangeRule()];

        foreach ($rules as $rule) {
            foreach (['-123', '12.5', 'abc', ' 12', '', 12.0, -123, true, null, [1]] as $value) {
                try {
                    $rule->validate($value, ['1', '5']);
                    $this->fail('A value that is not made of digits must be rejected.');
                } catch (ValidationException $e) {
                    $this->assertSame('Value must contain only digits.', $e->getMessage());
                }
            }
        }
    }

    #[Test]
    public function shouldRejectACountParameterThatIsNotAWholeNumber(): void
    {
        $rules = [new LengthRule(), new MaxLengthRule(), new MinLengthRule(), new MaxItemsRule(), new MinItemsRule()];

        foreach ($rules as $rule) {
            foreach (['abc', '-1', '1.5'] as $parameter) {
                try {
                    $rule->validate($rule instanceof MaxItemsRule || $rule instanceof MinItemsRule ? [1] : 'a', [$parameter]);
                    $this->fail('A count that is not a whole number must be rejected.');
                } catch (ValidationException $e) {
                    $this->assertSame('Invalid parameter: ' . $parameter, $e->getMessage());
                }
            }

            try {
                $rule->validate($rule instanceof MaxItemsRule || $rule instanceof MinItemsRule ? [1] : 'a', ['']);
                $this->fail('A count that is empty must be rejected.');
            } catch (ValidationException $e) {
                $this->assertStringContainsString('must be specified', $e->getMessage());
            }
        }
    }

    #[Test]
    public function shouldAcceptZeroAsACountParameter(): void
    {
        (new MinLengthRule())->validate('', ['0']);
        (new MinItemsRule())->validate([], ['0']);
        (new MaxLengthRule())->validate('', ['0']);
        (new MaxItemsRule())->validate([], ['0']);
        (new LengthRule())->validate('', ['0']);

        (new MinLengthRule())->validate('abc', ['3']);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function shouldRejectANumericParameterThatIsNotANumber(): void
    {
        $rules = [new GreaterThanRule(), new GreaterThanOrEqualRule(), new LessThanRule(), new LessThanOrEqualRule(), new MultipleOfRule()];

        foreach ($rules as $rule) {
            foreach (['abc', '', '1e999'] as $parameter) {
                try {
                    $rule->validate(5, [$parameter]);
                    $this->fail('A number that is not a number must be rejected.');
                } catch (ValidationException $e) {
                    $this->assertSame('Invalid parameter: ' . $parameter, $e->getMessage());
                }
            }
        }

        foreach ([['a', '10'], ['1', 'b']] as $parameters) {
            try {
                (new RangeRule())->validate(5, $parameters);
                $this->fail('A range that is not numeric must be rejected.');
            } catch (ValidationException $e) {
                $this->assertSame('Invalid parameter: ' . ($parameters[0] === 'a' ? 'a' : 'b'), $e->getMessage());
            }
        }

        (new GreaterThanRule())->validate(3, ['2.5']);
    }

    #[Test]
    public function shouldRejectAMultipleThatIsTooLongToCalculate(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid parameter: 123456789012345678');

        (new MultipleOfRule())->validate(5, ['123456789012345678']);
    }

    #[Test]
    public function shouldKeepTheDateErrorWhenTheValueIsNotADateButTheParameterIs(): void
    {
        $rules = [new AfterRule(), new AfterOrEqualRule(), new BeforeRule(), new BeforeOrEqualRule(), new DateEqualsRule()];

        foreach ($rules as $rule) {
            try {
                $rule->validate('not a date', ['2024-01-01']);
                $this->fail('A value that is not a date must be rejected.');
            } catch (ValidationException $e) {
                $this->assertSame('Invalid date format.', $e->getMessage());
            }
        }
    }

    #[Test]
    public function shouldRequireTheVersionOfTheUuidWhenItIsAParameter(): void
    {
        $rule = new UuidRule();
        $v4 = '3b241101-e2bb-4255-8caf-4136c566a962';
        $v7 = '018f4b0a-7b6e-7c3a-8f5e-2b4f6a7c8d9e';

        $rule->validate($v4, ['4']);
        $rule->validate($v7, ['7']);

        foreach ([[$v7, '4', 'Invalid UUID format.'], ['00000000-0000-0000-0000-000000000000', '4', 'Invalid UUID format.'], [$v4, '9', 'Invalid parameter: 9'], [$v4, '0', 'Invalid parameter: 0'], [$v4, 'x', 'Invalid parameter: x']] as [$value, $version, $message]) {
            try {
                $rule->validate($value, [$version]);
                $this->fail('A UUID that is not of the version must be rejected.');
            } catch (ValidationException $e) {
                $this->assertSame($message, $e->getMessage());
            }
        }
    }

    #[Test]
    public function shouldOnlyAcceptTheSchemesThatAreParameters(): void
    {
        $rule = new UrlRule();

        $rule->validate('https://example.com', ['http', 'https']);
        $rule->validate('HTTP://example.com', ['http']);
        $rule->validate('http://münchen.de', ['http']);

        foreach ([['ftp://example.com/a', 'http', 'Invalid URL format.'], ['javascript://x/%0Aalert(1)', 'http', 'Invalid URL format.'], ['not a url', 'http', 'Invalid URL format.'], ['http://example.com', '1http', 'Invalid parameter: 1http'], ['http://example.com', '', 'Invalid parameter: ']] as [$value, $scheme, $message]) {
            try {
                $rule->validate($value, [$scheme]);
                $this->fail('A URL with another scheme must be rejected.');
            } catch (ValidationException $e) {
                $this->assertSame($message, $e->getMessage());
            }
        }
    }

    #[Test]
    public function shouldOnlyAcceptAbsoluteDatesInIso8601AsValue(): void
    {
        $rule = new AfterRule();

        foreach (['2024-05-01', '2024-05-01T10:30', '2024-05-01T10:30:15', '2024-05-01 10:30:15', '2024-05-01T10:30:15.123Z', '2024-05-01T10:30:15+03:00', '2024-05-01T10:30-0300', '2024-02-29'] as $value) {
            $rule->validate($value, ['2000-01-01']);
        }
        $rule->validate(Carbon::parse('2024-05-01'), ['2000-01-01']);

        foreach (['tomorrow', 'next monday', 'now', '+1 day', 'last day of month', '01/02/2024', '10 September 2000', '2024-5-1', '2024-05-01T', '2024-02-30', '2023-02-29', '2024-13-01', '2024-05-01T24:00', '2024-05-01T10:60', '2024-05-01T10:30:60', '2024-05-01T10:00+99:99', '', 20240501, null, true, [], new stdClass()] as $value) {
            try {
                $rule->validate($value, ['2000-01-01']);
                $this->fail('A value that is not an absolute date must be rejected.');
            } catch (ValidationException $e) {
                $this->assertSame('Invalid date format.', $e->getMessage());
            }
        }
    }

    #[Test]
    public function shouldCompareWithTheCurrentMomentWhenTheDateIsNow(): void
    {
        Carbon::setTestNow('2024-06-15 12:00:00');

        try {
            (new AfterOrEqualRule())->validate('2024-06-15 12:00:00', ['now']);
            (new BeforeOrEqualRule())->validate('2024-06-15 12:00:00', ['now']);
            (new DateEqualsRule())->validate('2024-06-15 12:00:00', ['now']);
            $this->addToAssertionCount(3);
        } finally {
            Carbon::setTestNow();
        }
    }

    #[Test]
    public function shouldCastWhatHasTextAndRejectWhatDoesNot(): void
    {
        $text = new class () implements Stringable {
            public function __toString(): string
            {
                return 'its text';
            }
        };
        $rule = new StringRule();

        $this->assertSame('its text', $rule->cast($text));

        $resource = fopen('php://memory', 'r');
        $this->assertIsResource($resource);

        foreach ([[new stdClass(), 'Cannot cast object to string. Object does not implement __toString().'], [$resource, 'Cannot cast resource to string.']] as [$value, $message]) {
            try {
                $rule->cast($value);
                $this->fail('A value that has no text must be rejected.');
            } catch (CastingException $e) {
                $this->assertSame($message, $e->getMessage());
            }
        }

        fclose($resource);

        // A resource that was closed is not one for PHP functions.
        try {
            $rule->cast($resource);
            $this->fail('A closed resource must be rejected.');
        } catch (CastingException $e) {
            $this->assertSame('Cannot cast value of type resource (closed) to string.', $e->getMessage());
        }
    }

    #[Test]
    public function shouldRejectAValueThatCanNotBeEncodedToJson(): void
    {
        $this->expectException(TransformationException::class);
        $this->expectExceptionMessage('Error encoding the value to JSON: ');

        (new JsonEncodeRule())->transform(NAN);
    }

    #[Test]
    public function shouldRejectJsonThatIsNotAnArray(): void
    {
        $this->expectException(TransformationException::class);
        $this->expectExceptionMessage('JSON decoded value is not an array.');

        (new JsonToArrayRule())->transform('"text"');
    }

    #[Test]
    public function shouldRejectTheRulesOfATransliterationThatDoNotExist(): void
    {
        // PHP warns that the transliterator can not be created.
        set_error_handler(static fn (): bool => true);

        try {
            $this->expectException(TransformationException::class);
            $this->expectExceptionMessage('Error converting the value to ASCII.');

            (new TransliterateRule())->transform('abc', ['Not-A-Real-Rule']);
        } finally {
            restore_error_handler();
        }
    }

    #[Test]
    public function shouldTransformWithTheSlugRule(): void
    {
        $this->assertSame('hello-world', (new SlugRule())->transform('Héllo  World!'));
    }
}
