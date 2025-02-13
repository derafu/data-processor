<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Caster;

use Derafu\DataProcessor\Contract\CasterRuleInterface;
use Derafu\DataProcessor\Exception\CastingException;

final class StringRule implements CasterRuleInterface
{
    public function cast(mixed $value, array $parameters = []): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        if (is_array($value)) {
            throw new CastingException('Cannot cast array to string.');
        }

        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return $value->__toString();
            }
            throw new CastingException(
                'Cannot cast object to string. Object does not implement __toString().'
            );
        }

        if (is_resource($value)) {
            throw new CastingException('Cannot cast resource to string.');
        }

        throw new CastingException(sprintf(
            'Cannot cast value of type %s to string.',
            get_debug_type($value)
        ));
    }
}
