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

final class IbanRule implements ValidatorRuleInterface
{
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

        // Check modulo 97.
        if (bcmod($converted, '97') !== '1') {
            throw new ValidationException('Invalid IBAN checksum.');
        }
    }
}
