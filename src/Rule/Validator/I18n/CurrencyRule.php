<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\I18n;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

/**
 * Validates an ISO 4217 alphabetic currency code, in uppercase or lowercase.
 *
 * The list is the table "List one: Currency, fund and precious metal codes" of
 * the maintenance agency of ISO 4217 (SIX), published on 2026-09-17
 * (list-one.xml). It has the codes in force, with the funds codes (as `CLF`).
 * It does not have the codes of precious metals and the special ones that are
 * not a currency: `XAG`, `XAU`, `XPD`, `XPT`, `XDR`, `XTS` and `XXX`. The list
 * is kept in the code, because ICU also has the withdrawn currencies. It must
 * be reviewed with each amendment of the standard.
 *
 * @see https://www.six-group.com/dam/download/financial-information/data-center/iso-currrency/lists/list-one.xml
 * @see https://www.six-group.com/en/products-services/financial-information/market-reference-data/data-standards/iso-currency.html
 */
final class CurrencyRule implements ValidatorRuleInterface
{
    /**
     * ISO 4217 currency codes.
     */
    private const CURRENCIES = [
        'AED', 'AFN', 'ALL', 'AMD', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM',
        'BBD', 'BDT', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BOV', 'BRL', 'BSD',
        'BTN', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF', 'CHE', 'CHF', 'CHW', 'CLF',
        'CLP', 'CNY', 'COP', 'COU', 'CRC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK',
        'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL',
        'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HTG', 'HUF',
        'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES',
        'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP',
        'LKR', 'LRD', 'LSL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT',
        'MOP', 'MRU', 'MUR', 'MVR', 'MWK', 'MXN', 'MXV', 'MYR', 'MZN', 'NAD',
        'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP',
        'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD',
        'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLE', 'SOS', 'SRD', 'SSP', 'STN',
        'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD',
        'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'USN', 'UYI', 'UYU', 'UYW', 'UZS',
        'VED', 'VES', 'VND', 'VUV', 'WST', 'XAD', 'XAF', 'XBA', 'XBB', 'XBC',
        'XBD', 'XCD', 'XCG', 'XOF', 'XPF', 'XSU', 'XUA', 'YER', 'ZAR', 'ZMW',
        'ZWG',
    ];

    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        $value = strtoupper($value);
        if (!in_array($value, self::CURRENCIES, true)) {
            throw new ValidationException('Invalid currency code.');
        }
    }
}
