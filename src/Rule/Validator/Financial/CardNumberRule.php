<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Financial;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

final class CardNumberRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        // Eliminar espacios y guiones.
        $number = preg_replace('/\D/', '', $value);

        // Verificar longitud.
        if (strlen($number) < 13 || strlen($number) > 19) {
            throw new ValidationException('Invalid card number length.');
        }

        // Algoritmo de Luhn.
        $sum = 0;
        $length = strlen($number);
        $parity = $length % 2;

        for ($i = $length - 1; $i >= 0; $i--) {
            $digit = (int)$number[$i];
            if ($i % 2 === $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        if ($sum % 10 !== 0) {
            throw new ValidationException('Invalid card number checksum.');
        }
    }
}
