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

use Derafu\DataProcessor\Contract\ProcessorInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Processor;
use Derafu\DataProcessor\ProcessorFactory;
use Derafu\DataProcessor\Registrar\DefaultRuleRegistrar;
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

    public static function uuidValidationProvider(): array
    {
        return [
            'valid_uuid_v4' => [
                '123e4567-e89b-42d3-a456-426614174000',
                true,
            ],
            'invalid_uuid_v4' => [
                '123e4567-e89b-12d3-a456-426614174000',
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
        ];
    }

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
        ];
    }

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
        ];
    }
}
