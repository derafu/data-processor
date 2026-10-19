<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Financial;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

/**
 * Validates an IBAN (ISO 13616): the country, the length of that country and the
 * check digits (modulo 97). It accepts spaces and lowercase letters.
 *
 * The lengths are the ones of the IBAN Registry of SWIFT, the registration
 * authority of ISO 13616, release 99 (December 2024), with the 89 countries
 * that have an IBAN. The registry is not published in a format that can be
 * read by a program (it is a PDF), so the table is kept in the code and it must
 * be reviewed with each new release of the registry. A territory uses the code
 * of the country that it depends on (French Guiana is `FR`).
 *
 * @see https://www.swift.com/standards/data-standards/iban-international-bank-account-number
 */
final class IbanRule implements ValidatorRuleInterface
{
    /**
     * Length of the IBAN of each country, by its ISO 3166-1 alpha-2 code.
     */
    private const LENGTHS = [
        'AD' => 24, 'AE' => 23, 'AL' => 28, 'AT' => 20, 'AZ' => 28, 'BA' => 20,
        'BE' => 16, 'BG' => 22, 'BH' => 22, 'BI' => 27, 'BR' => 29, 'BY' => 28,
        'CH' => 21, 'CR' => 22, 'CY' => 28, 'CZ' => 24, 'DE' => 22, 'DJ' => 27,
        'DK' => 18, 'DO' => 28, 'EE' => 20, 'EG' => 29, 'ES' => 24, 'FI' => 18,
        'FK' => 18, 'FO' => 18, 'FR' => 27, 'GB' => 22, 'GE' => 22, 'GI' => 23,
        'GL' => 18, 'GR' => 27, 'GT' => 28, 'HN' => 28, 'HR' => 21, 'HU' => 28,
        'IE' => 22, 'IL' => 23, 'IQ' => 23, 'IS' => 26, 'IT' => 27, 'JO' => 30,
        'KW' => 30, 'KZ' => 20, 'LB' => 28, 'LC' => 32, 'LI' => 21, 'LT' => 20,
        'LU' => 20, 'LV' => 21, 'LY' => 25, 'MC' => 27, 'MD' => 24, 'ME' => 22,
        'MK' => 19, 'MN' => 20, 'MR' => 27, 'MT' => 31, 'MU' => 30, 'NI' => 28,
        'NL' => 18, 'NO' => 15, 'OM' => 23, 'PK' => 24, 'PL' => 28, 'PS' => 29,
        'PT' => 25, 'QA' => 29, 'RO' => 24, 'RS' => 22, 'RU' => 33, 'SA' => 24,
        'SC' => 31, 'SD' => 18, 'SE' => 24, 'SI' => 19, 'SK' => 24, 'SM' => 27,
        'SO' => 23, 'ST' => 25, 'SV' => 28, 'TL' => 23, 'TN' => 24, 'TR' => 26,
        'UA' => 29, 'VA' => 22, 'VG' => 24, 'XK' => 20, 'YE' => 30,
    ];

    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        // Remove spaces and convert to uppercase.
        $iban = strtoupper(str_replace(' ', '', $value));

        // Check basic format.
        if (!preg_match('/^[A-Z]{2}[0-9A-Z]{2,}$/', $iban)) {
            throw new ValidationException('Invalid IBAN format.');
        }

        // The country must have IBAN and the IBAN must have its length.
        $length = self::LENGTHS[substr($iban, 0, 2)] ?? null;
        if ($length === null) {
            throw new ValidationException('Invalid country code in IBAN.');
        }

        if (strlen($iban) !== $length) {
            throw new ValidationException('Invalid IBAN length.');
        }

        // Move the first 4 characters to the end.
        $moved = substr($iban, 4) . substr($iban, 0, 4);

        // Convert letters to numbers (A=10, B=11, etc).
        $converted = '';
        for ($i = 0; $i < strlen($moved); $i++) {
            $char = $moved[$i];
            if (ctype_alpha($char)) {
                $converted .= (ord($char) - 55);
            } else {
                $converted .= $char;
            }
        }

        // Check modulo 97, in chunks that fit in an integer.
        $remainder = 0;
        foreach (str_split($converted, 7) as $chunk) {
            $remainder = (int)($remainder . $chunk) % 97;
        }

        if ($remainder !== 1) {
            throw new ValidationException('Invalid IBAN checksum.');
        }
    }
}
