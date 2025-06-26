<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Internet;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

final class HostnameRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        // Permitir localhost.
        if ($value === 'localhost') {
            return;
        }

        // Convertir a ASCII para manejar nombres de dominio internacionalizados.
        if (function_exists('idn_to_ascii')) {
            $value = idn_to_ascii($value) ?: $value;
        }

        // RFC 1034/1035.
        $pattern = '/^(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i';

        if (!preg_match($pattern, $value)) {
            throw new ValidationException('Invalid hostname format.');
        }

        // Verificar longitud máxima.
        if (strlen($value) > 253) {
            throw new ValidationException('Hostname too long.');
        }

        // Verificar longitud de cada parte.
        foreach (explode('.', $value) as $part) {
            if (strlen($part) > 63) {
                throw new ValidationException('Hostname part too long.');
            }
        }
    }
}
