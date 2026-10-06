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

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DateTimeInterface;
use Derafu\DataProcessor\Contract\CasterRuleInterface;
use Derafu\DataProcessor\Exception\CastingException;

final class DateTimeRule implements CasterRuleInterface
{
    public function cast(mixed $value, array $parameters = []): Carbon
    {
        if ($value === null) {
            throw new CastingException('Invalid datetime format: null is not valid.');
        }
        if (is_numeric($value)) {
            throw new CastingException('Invalid datetime format: numeric is not valid.');
        }
        if ($value instanceof Carbon) {
            return $value;
        }

        if (!is_string($value) && !$value instanceof DateTimeInterface) {
            throw new CastingException([
                'Invalid datetime format: {type} is not valid.',
                'type' => get_debug_type($value),
            ]);
        }

        try {
            return Carbon::parse($value);
        } catch (InvalidFormatException $e) {
            throw new CastingException([
                'Invalid datetime format: {reason}',
                'reason' => $e->getMessage(),
            ]);
        }
    }
}
