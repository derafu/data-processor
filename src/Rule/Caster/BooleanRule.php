<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Caster;

use Derafu\DataProcessor\Contract\CasterRuleInterface;
use Derafu\DataProcessor\Exception\CastingException;

/**
 * Casts to boolean only what says clearly yes or no: the booleans, `null`, `1`
 * and `0`, and the texts `true`, `1`, `yes` and `on`, or `false`, `0`, `no`,
 * `off` and the empty text (without distinguishing case). Any other value is
 * an error, it is not `true` just because it is not empty.
 */
final class BooleanRule implements CasterRuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function cast(mixed $value, array $parameters = []): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null || $value === 0) {
            return false;
        }

        if ($value === 1) {
            return true;
        }

        if (is_string($value)) {
            $text = strtolower($value);
            if (in_array($text, ['false', '0', 'no', 'off', ''], true)) {
                return false;
            }
            if (in_array($text, ['true', '1', 'yes', 'on'], true)) {
                return true;
            }
        }

        throw new CastingException('Cannot cast value to boolean.');
    }
}
