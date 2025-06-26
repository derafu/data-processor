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

        // Eliminar espacios y convertir a mayúsculas.
        $iban = strtoupper(str_replace(' ', '', $value));

        // Verificar formato básico.
        if (!preg_match('/^[A-Z]{2}[0-9A-Z]{2,}$/', $iban)) {
            throw new ValidationException('Invalid IBAN format.');
        }

        // Mover los primeros 4 caracteres al final.
        $moved = substr($iban, 4) . substr($iban, 0, 4);

        // Convertir letras a números (A=10, B=11, etc).
        $converted = '';
        for ($i = 0; $i < strlen($moved); $i++) {
            $char = $moved[$i];
            if (ctype_alpha($char)) {
                $converted .= (ord($char) - 55);
            } else {
                $converted .= $char;
            }
        }

        // Verificar módulo 97.
        if (bcmod($converted, '97') !== '1') {
            throw new ValidationException('Invalid IBAN checksum.');
        }
    }
}
