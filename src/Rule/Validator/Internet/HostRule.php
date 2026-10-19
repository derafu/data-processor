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

/**
 * Validates a host: a host name (as `hostname`) or an IP address (as `ip`).
 *
 * The rules of a field are all required, so "host name or IP" can not be
 * written with `hostname` and `ip`. Without parameters any IP version is valid.
 * With `v4` or `v6` only an IP of that version is valid, a host name is valid
 * anyway (`host:v4`).
 */
final class HostRule implements ValidatorRuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        $version = $parameters[0] ?? null;
        if ($version !== null && $version !== 'v4' && $version !== 'v6') {
            throw new ValidationException([
                'Invalid parameter: {parameter}',
                'parameter' => $version,
            ]);
        }

        foreach ([new IpRule(), new HostnameRule()] as $rule) {
            try {
                $rule->validate($value, $rule instanceof IpRule ? $parameters : []);

                return;
            } catch (ValidationException) {
                // It is not that kind of host, the next one is tried.
            }
        }

        throw new ValidationException('Invalid host format.');
    }
}
