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
use Derafu\DataProcessor\Rule\Validator\Date\AfterOrEqualRule;
use Derafu\DataProcessor\Rule\Validator\Date\AfterRule;
use Derafu\DataProcessor\Rule\Validator\Date\BeforeOrEqualRule;
use Derafu\DataProcessor\Rule\Validator\Date\BeforeRule;
use Derafu\DataProcessor\Rule\Validator\Date\BetweenRule;
use Derafu\DataProcessor\Rule\Validator\Date\DateEqualsRule;
use Derafu\DataProcessor\Rule\Validator\Date\DateFormatRule;
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
use Derafu\DataProcessor\Rule\Validator\String\EndsWithRule;
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
            'DigitsRangeRule' => [new DigitsRangeRule()],
            'DigitsRule' => [new DigitsRule()],
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
            [new IbanRule(), '1234', 'Invalid IBAN format.'],
            [new IbanRule(), 'XX00', 'Invalid IBAN checksum.'],
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
                $this->assertSame('Invalid date format.', $e->getMessage());
            }
        }

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid date format.');

        (new BetweenRule())->validate('2024-06-01', ['not a date', '2024-12-31']);
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
