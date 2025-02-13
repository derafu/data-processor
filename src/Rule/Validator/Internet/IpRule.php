<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Internet;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

final class IpRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        $version = $parameters[0] ?? null;
        $flags = match($version) {
            'v4' => FILTER_FLAG_IPV4,
            'v6' => FILTER_FLAG_IPV6,
            null => 0,
            default => throw new ValidationException('Invalid IP version specified.')
        };

        if (!filter_var($value, FILTER_VALIDATE_IP, $flags)) {
            throw new ValidationException(
                $version
                    ? "Invalid IPv{$version} format."
                    : 'Invalid IP format.'
            );
        }
    }
}
