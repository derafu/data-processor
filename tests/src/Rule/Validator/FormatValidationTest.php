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
use Derafu\DataProcessor\Rule\Validator\String\ColorRule;
use Derafu\DataProcessor\RuleParser;
use Derafu\DataProcessor\RuleRegistry;
use Derafu\DataProcessor\RuleResolver;
use Derafu\DataProcessor\RuleSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Processor::class)]
#[CoversClass(DefaultRuleRegistrar::class)]
#[CoversClass(ProcessorFactory::class)]
#[CoversClass(RuleRegistry::class)]
#[CoversClass(RuleResolver::class)]
#[CoversClass(RuleParser::class)]
#[CoversClass(EmailRule::class)]
#[CoversClass(UrlRule::class)]
#[CoversClass(IpRule::class)]
#[CoversClass(UuidRule::class)]
#[CoversClass(LanguageRule::class)]
#[CoversClass(LocaleRule::class)]
#[CoversClass(CountryRule::class)]
#[CoversClass(CurrencyRule::class)]
#[CoversClass(TimezoneRule::class)]
#[CoversClass(IbanRule::class)]
#[CoversClass(BicRule::class)]
#[CoversClass(CardNumberRule::class)]
#[CoversClass(HostnameRule::class)]
#[CoversClass(HostRule::class)]
#[CoversClass(WeekRule::class)]
#[CoversClass(TimeRule::class)]
#[CoversClass(MonthRule::class)]
#[CoversClass(DatetimeLocalRule::class)]
#[CoversClass(ColorRule::class)]
#[UsesClass(RuleSet::class)]
final class FormatValidationTest extends TestCase
{
    private ProcessorInterface $processor;

    protected function setUp(): void
    {
        $this->processor = ProcessorFactory::create();
    }

    #[DataProvider('emailValidationProvider')]
    public function testEmailValidation(string $input, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $result = $this->processor->process($input, [
            'validate' => ['email'],
        ]);

        if ($shouldPass) {
            $this->assertSame($input, $result);
        }
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function emailValidationProvider(): array
    {
        return [
            'valid_email' => [
                'test@example.com',
                true,
            ],
            'valid_email_subdomain' => [
                'test@sub.example.com',
                true,
            ],
            'invalid_email_no_at' => [
                'testexample.com',
                false,
            ],
            'invalid_email_no_domain' => [
                'test@',
                false,
            ],
            'invalid_email_spaces' => [
                'test @example.com',
                false,
            ],
            'valid_email_internationalized_domain' => [
                'jose@exämple.com',
                true,
            ],
            'valid_email_punycode_domain' => [
                'jose@xn--exmple-cua.com',
                true,
            ],
            'invalid_email_unicode_local_part' => [
                'josé@example.com',
                false,
            ],
            'invalid_email_internationalized_domain_with_spaces' => [
                'jose@ex ämple.com',
                false,
            ],
        ];
    }

    #[DataProvider('htmlInputValidationProvider')]
    public function testHtmlInputValidation(string $input, string $rule, ?string $message): void
    {
        if ($message !== null) {
            $this->expectException(ValidationException::class);
            $this->expectExceptionMessage($message);
        }

        $result = $this->processor->process($input, [
            'validate' => [$rule],
        ]);

        $this->assertSame($input, $result);
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function htmlInputValidationProvider(): array
    {
        return [
            'color_lowercase' => ['#1a2b3c', 'color', null],
            'color_uppercase' => ['#1A2B3C', 'color', null],
            'color_short' => ['#fff', 'color', 'Invalid color format.'],
            'color_name' => ['red', 'color', 'Invalid color format.'],
            'color_not_hexadecimal' => ['#12345g', 'color', 'Invalid color format.'],
            'color_with_line_break' => ["#1a2b3c\n", 'color', 'Invalid color format.'],
            'month_valid' => ['2024-05', 'month', null],
            'month_thirteen' => ['2024-13', 'month', 'Invalid month format.'],
            'month_zero' => ['2024-00', 'month', 'Invalid month format.'],
            'month_year_zero' => ['0000-05', 'month', 'Invalid month format.'],
            'month_with_day' => ['2024-05-01', 'month', 'Invalid month format.'],
            'week_valid' => ['2024-W22', 'week', null],
            'week_53_in_a_year_with_52' => ['2024-W53', 'week', 'Invalid week format.'],
            'week_53_in_a_year_with_53' => ['2026-W53', 'week', null],
            'week_52_in_a_year_with_52' => ['2024-W52', 'week', null],
            'week_zero' => ['2024-W00', 'week', 'Invalid week format.'],
            'week_54' => ['2026-W54', 'week', 'Invalid week format.'],
            'week_year_zero' => ['0000-W01', 'week', 'Invalid week format.'],
            'week_lowercase' => ['2024-w22', 'week', 'Invalid week format.'],
            'time_minutes' => ['10:30', 'time', null],
            'time_seconds' => ['10:30:15', 'time', null],
            'time_midnight' => ['00:00', 'time', null],
            'time_hour_25' => ['25:00', 'time', 'Invalid time format.'],
            'time_minute_60' => ['10:60', 'time', 'Invalid time format.'],
            'time_second_60' => ['10:30:60', 'time', 'Invalid time format.'],
            'time_without_zero' => ['9:30', 'time', 'Invalid time format.'],
            'datetime_local_minutes' => ['2024-05-01T10:30', 'datetime_local', null],
            'datetime_local_seconds' => ['2024-05-01T10:30:15', 'datetime_local', null],
            'datetime_local_with_space' => ['2024-05-01 10:30', 'datetime_local', 'Invalid datetime-local format.'],
            'datetime_local_without_time' => ['2024-05-01', 'datetime_local', 'Invalid datetime-local format.'],
            'datetime_local_with_zone' => ['2024-05-01T10:30Z', 'datetime_local', 'Invalid datetime-local format.'],
            'datetime_local_date_does_not_exist' => ['2024-02-30T10:30', 'datetime_local', 'Invalid datetime-local format.'],
            'datetime_local_hour_25' => ['2024-05-01T25:00', 'datetime_local', 'Invalid datetime-local format.'],
        ];
    }

    #[DataProvider('urlValidationProvider')]
    public function testUrlValidation(string $input, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $result = $this->processor->process($input, [
            'validate' => ['url'],
        ]);

        if ($shouldPass) {
            $this->assertSame($input, $result);
        }
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function urlValidationProvider(): array
    {
        return [
            'valid_url_http' => [
                'http://example.com',
                true,
            ],
            'valid_url_https' => [
                'https://example.com',
                true,
            ],
            'valid_url_with_path' => [
                'https://example.com/path',
                true,
            ],
            'valid_url_with_query' => [
                'https://example.com?param=value',
                true,
            ],
            'valid_url_other_scheme' => [
                'ftp://example.com/a',
                true,
            ],
            'valid_url_idn' => [
                'http://münchen.de/a?b=1',
                true,
            ],
            'invalid_url_idn' => [
                'http://-münchen.de',
                false,
            ],
            'invalid_url_no_protocol' => [
                'example.com',
                false,
            ],
            'invalid_url_spaces' => [
                'https://example .com',
                false,
            ],
        ];
    }

    /**
     * @param list<string> $rules
     */
    #[DataProvider('ipValidationProvider')]
    public function testIpValidation(string $input, array $rules, bool $shouldPass): void
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

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function ipValidationProvider(): array
    {
        return [
            'valid_ipv4' => [
                '192.168.1.1',
                ['ip'],
                true,
            ],
            'valid_ipv6' => [
                '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
                ['ip'],
                true,
            ],
            'valid_ipv4_only' => [
                '192.168.1.1',
                ['ip:v4'],
                true,
            ],
            'invalid_ipv4_for_v6' => [
                '192.168.1.1',
                ['ip:v6'],
                false,
            ],
            'invalid_ip' => [
                '256.256.256.256',
                ['ip'],
                false,
            ],
        ];
    }

    #[DataProvider('uuidValidationProvider')]
    public function testUuidValidation(string $input, bool $shouldPass): void
    {
        if (!$shouldPass) {
            $this->expectException(ValidationException::class);
        }

        $result = $this->processor->process($input, [
            'validate' => ['uuid'],
        ]);

        if ($shouldPass) {
            $this->assertSame($input, $result);
        }
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function uuidValidationProvider(): array
    {
        return [
            'valid_uuid_v4' => [
                '123e4567-e89b-42d3-a456-426614174000',
                true,
            ],
            'valid_uuid_v1' => [
                '123e4567-e89b-12d3-a456-426614174000',
                true,
            ],
            'valid_uuid_v7' => [
                '018f4b0a-7b6e-7c3a-8f5e-2b4f6a7c8d9e',
                true,
            ],
            'valid_uuid_nil' => [
                '00000000-0000-0000-0000-000000000000',
                true,
            ],
            'valid_uuid_max' => [
                'FFFFFFFF-FFFF-FFFF-FFFF-FFFFFFFFFFFF',
                true,
            ],
            'invalid_uuid_version' => [
                '123e4567-e89b-92d3-a456-426614174000',
                false,
            ],
            'invalid_uuid_variant' => [
                '123e4567-e89b-42d3-c456-426614174000',
                false,
            ],
            'invalid_uuid_format' => [
                '123e4567-e89b-12d3-a456',
                false,
            ],
            'invalid_uuid_chars' => [
                '123e4567-e89b-12d3-a456-42661417400g',
                false,
            ],
        ];
    }

    /**
     * @param list<string> $rules
     */
    #[DataProvider('localeValidationProvider')]
    public function testLocaleValidation(string $input, array $rules, bool $shouldPass): void
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

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function localeValidationProvider(): array
    {
        return [
            'valid_language' => [
                'es',
                ['language'],
                true,
            ],
            'valid_language_with_region' => [
                'es_CL',
                ['language'],
                true,
            ],
            'valid_language_three_letters' => [
                'fil',
                ['language'],
                true,
            ],
            'valid_language_with_hyphen_region' => [
                'es-CL',
                ['language'],
                true,
            ],
            'invalid_language_region' => [
                'es_ZZ',
                ['language'],
                false,
            ],
            'invalid_language_long_region' => [
                'es_ZZZZ',
                ['language'],
                false,
            ],
            'invalid_language_empty_region' => [
                'es_',
                ['language'],
                false,
            ],
            'invalid_language' => [
                'xx',
                ['language'],
                false,
            ],
            'valid_locale' => [
                'es_CL',
                ['locale'],
                true,
            ],
            'valid_locale_with_hyphen' => [
                'es-CL',
                ['locale'],
                true,
            ],
            'valid_locale_with_script' => [
                'sr-Latn-RS',
                ['locale'],
                true,
            ],
            'invalid_locale' => [
                'es_XX',
                ['locale'],
                false,
            ],
            'valid_country' => [
                'CL',
                ['country'],
                true,
            ],
            'invalid_country' => [
                'XX',
                ['country'],
                false,
            ],
        ];
    }

    /**
     * @param list<string> $rules
     */
    #[DataProvider('currencyValidationProvider')]
    public function testCurrencyValidation(string $input, array $rules, bool $shouldPass): void
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

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function currencyValidationProvider(): array
    {
        return [
            'valid_currency' => [
                'USD',
                ['currency'],
                true,
            ],
            'valid_currency_lowercase' => [
                'eur',
                ['currency'],
                true,
            ],
            'valid_currency_fund' => [
                'CLF',
                ['currency'],
                true,
            ],
            'valid_currency_new' => [
                'ZWG',
                ['currency'],
                true,
            ],
            'invalid_currency_withdrawn' => [
                'HRK',
                ['currency'],
                false,
            ],
            'invalid_currency_metal' => [
                'XAU',
                ['currency'],
                false,
            ],
            'invalid_currency' => [
                'AAA',
                ['currency'],
                false,
            ],
            'invalid_currency_length' => [
                'USDD',
                ['currency'],
                false,
            ],
        ];
    }

    /**
     * @param list<string> $rules
     */
    #[DataProvider('timeZoneValidationProvider')]
    public function testTimeZoneValidation(string $input, array $rules, bool $shouldPass): void
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

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function timeZoneValidationProvider(): array
    {
        return [
            'valid_timezone_continent_city' => [
                'America/Santiago',
                ['timezone'],
                true,
            ],
            'valid_timezone_offset' => [
                'UTC-4', // No es oficial de DateTimeZone::listIdentifiers().
                ['timezone'],
                true,
            ],
            'invalid_timezone' => [
                'Invalid/Timezone',
                ['timezone'],
                false,
            ],
            'valid_timezone_offset_minimum' => [
                'UTC-12',
                ['timezone'],
                true,
            ],
            'valid_timezone_offset_maximum' => [
                'UTC+14',
                ['timezone'],
                true,
            ],
            'invalid_timezone_offset_below_minimum' => [
                'UTC-13',
                ['timezone'],
                false,
            ],
            'invalid_timezone_offset_far_below_minimum' => [
                'UTC-14',
                ['timezone'],
                false,
            ],
            'invalid_timezone_offset_above_maximum' => [
                'UTC+15',
                ['timezone'],
                false,
            ],
        ];
    }

    /**
     * @param list<string> $rules
     */
    #[DataProvider('financialValidationProvider')]
    public function testFinancialValidation(string $input, array $rules, bool $shouldPass): void
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

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function financialValidationProvider(): array
    {
        return [
            'valid_iban' => [
                'DE89370400440532013000',
                ['iban'],
                true,
            ],
            'invalid_iban' => [
                'INVALID123456',
                ['iban'],
                false,
            ],
            'valid_bic' => [
                'DEUTDEFF',
                ['bic'],
                true,
            ],
            'valid_bic_with_branch' => [
                'DEUTDEFF500',
                ['bic'],
                true,
            ],
            'invalid_bic' => [
                'INVALID12',
                ['bic'],
                false,
            ],
            'valid_card_number' => [
                '4532015112830366',
                ['card_number'],
                true,
            ],
            'invalid_card_number' => [
                '1234567812345678',
                ['card_number'],
                false,
            ],
            'valid_card_number_with_spaces' => [
                '4532 0151 1283 0366',
                ['card_number'],
                true,
            ],
            'valid_card_number_with_dashes' => [
                '4532-0151-1283-0366',
                ['card_number'],
                true,
            ],
            'invalid_card_number_with_letters_around' => [
                'abc4532015112830366xyz',
                ['card_number'],
                false,
            ],
            'invalid_card_number_with_other_separators' => [
                '4532.0151.1283.0366',
                ['card_number'],
                false,
            ],
            'valid_iban_with_spaces' => [
                'ES91 2100 0418 4502 0005 1332',
                ['iban'],
                true,
            ],
            'valid_iban_with_letters_in_the_account' => [
                'GB82WEST12345698765432',
                ['iban'],
                true,
            ],
            'invalid_iban_checksum' => [
                'GB82WEST12345698765433',
                ['iban'],
                false,
            ],
        ];
    }

    /**
     * @param list<string> $rules
     */
    #[DataProvider('hostValidationProvider')]
    public function testHostValidation(string $input, array $rules, bool $shouldPass): void
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

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    public static function hostValidationProvider(): array
    {
        return [
            'valid_hostname' => [
                'example.com',
                ['hostname'],
                true,
            ],
            'valid_hostname_subdomain' => [
                'sub.example.com',
                ['hostname'],
                true,
            ],
            'invalid_hostname' => [
                'not_a_hostname',
                ['hostname'],
                false,
            ],
            'valid_hostname_unicode' => [
                'méxico.com',
                ['hostname'],
                true,
            ],
            'valid_hostname_local' => [
                'localhost',
                ['hostname'],
                true,
            ],
            'invalid_hostname_numeric_last_label' => [
                'example.123',
                ['hostname'],
                false,
            ],
            'invalid_hostname_ipv4' => [
                '1.2.3.4',
                ['hostname'],
                false,
            ],
            'valid_hostname_label_with_digits' => [
                'a1.b2',
                ['hostname'],
                true,
            ],
            'valid_host_name' => [
                'example.com',
                ['host'],
                true,
            ],
            'valid_host_ipv4' => [
                '1.2.3.4',
                ['host'],
                true,
            ],
            'valid_host_ipv6' => [
                '2001:db8::1',
                ['host'],
                true,
            ],
            'invalid_host' => [
                'example.123',
                ['host'],
                false,
            ],
            'invalid_host_spaces' => [
                'not a host',
                ['host'],
                false,
            ],
            'valid_host_ipv4_only' => [
                '1.2.3.4',
                ['host:v4'],
                true,
            ],
            'valid_host_name_with_ip_version' => [
                'example.com',
                ['host:v4'],
                true,
            ],
            'invalid_host_ipv6_for_v4' => [
                '2001:db8::1',
                ['host:v4'],
                false,
            ],
            'invalid_host_ipv4_for_v6' => [
                '1.2.3.4',
                ['host:v6'],
                false,
            ],
            'valid_hostname_single_label' => [
                'myhost',
                ['hostname'],
                true,
            ],
            'invalid_hostname_single_numeric_label' => [
                '123',
                ['hostname'],
                false,
            ],
        ];
    }
}
