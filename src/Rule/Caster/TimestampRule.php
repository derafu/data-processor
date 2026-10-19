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
use Derafu\DataProcessor\Trait\Rule\AbsoluteDateTrait;

final class TimestampRule implements CasterRuleInterface
{
    use AbsoluteDateTrait;

    public function cast(mixed $value, array $parameters = []): int
    {
        if ($value === null) {
            throw new CastingException('Invalid timestamp format: null is not valid.');
        }
        if (is_numeric($value)) {
            return (int)$value;
        }

        if (!is_string($value) && !$value instanceof DateTimeInterface) {
            throw new CastingException([
                'Invalid timestamp format: {type} is not valid.',
                'type' => get_debug_type($value),
            ]);
        }

        if (is_string($value) && !$this->isAbsoluteDate($value)) {
            throw new CastingException('Invalid timestamp format: not an absolute ISO 8601 date.');
        }

        try {
            return Carbon::parse($value)->getTimestamp();
        } catch (InvalidFormatException $e) {
            throw new CastingException([
                'Invalid timestamp format: {reason}',
                'reason' => $e->getMessage(),
            ]);
        }
    }
}
