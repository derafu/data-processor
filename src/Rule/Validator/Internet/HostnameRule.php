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

        // Convert to ASCII to handle internationalized domain names.
        if (function_exists('idn_to_ascii')) {
            $value = idn_to_ascii($value) ?: $value;
        }

        // RFC 952 and RFC 1123: one or more labels. A name of one label (as
        // `localhost` or `myhost`) is a host name, but the label of the right
        // can not be only digits, because RFC 1123 (section 2.1) says that a
        // host name can not look like an IP address (`1.2.3.4`).
        $pattern = '/^(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i';

        $labels = explode('.', $value);
        if (!preg_match($pattern, $value) || ctype_digit($labels[array_key_last($labels)])) {
            throw new ValidationException('Invalid hostname format.');
        }

        // Check maximum length.
        if (strlen($value) > 253) {
            throw new ValidationException('Hostname too long.');
        }

        // Check the length of each part.
        foreach ($labels as $part) {
            if (strlen($part) > 63) {
                throw new ValidationException('Hostname part too long.');
            }
        }
    }
}
