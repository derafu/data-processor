<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Id;

use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

/**
 * Validates a UUID in its text form (RFC 9562, which replaces RFC 4122).
 *
 * It accepts the versions 1 to 8 with the RFC 4122 variant, and the nil
 * (`00000000-0000-0000-0000-000000000000`) and max
 * (`ffffffff-ffff-ffff-ffff-ffffffffffff`) UUIDs. With a parameter, only that
 * version is valid (`uuid:4`), and the nil and max UUIDs have no version.
 *
 * @see https://www.rfc-editor.org/rfc/rfc9562
 */
final class UuidRule implements ValidatorRuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        $version = $this->version($parameters);

        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-([1-8])[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        if (preg_match($pattern, $value, $matches)) {
            if ($version === null || (int) $matches[1] === $version) {
                return;
            }
        } elseif ($version === null && preg_match('/^(0{8}-0{4}-0{4}-0{4}-0{12}|f{8}-f{4}-f{4}-f{4}-f{12})$/i', $value)) {
            return;
        }

        throw new ValidationException('Invalid UUID format.');
    }

    /**
     * Reads the version that is required, if any.
     *
     * @param list<string> $parameters The parameters of the rule.
     * @return int|null The version, or `null` if any version is valid.
     * @throws ValidationException If the parameter is not a version from 1 to 8.
     */
    private function version(array $parameters): ?int
    {
        if (!isset($parameters[0])) {
            return null;
        }

        if (!preg_match('/^[1-8]$/', $parameters[0])) {
            throw new ValidationException([
                'Invalid parameter: {parameter}',
                'parameter' => $parameters[0],
            ]);
        }

        return (int) $parameters[0];
    }
}
