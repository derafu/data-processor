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

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Derafu\DataProcessor\Contract\CasterRuleInterface;
use Derafu\DataProcessor\Exception\CastingException;
use TypeError;

final class DateTimeRule implements CasterRuleInterface
{
    public function cast(mixed $value, array $parameters = []): Carbon
    {
        try {
            if ($value === null) {
                throw new InvalidFormatException('null is not valid.');
            }
            if (is_numeric($value)) {
                throw new InvalidFormatException('numeric is not valid.');
            }
            if ($value instanceof Carbon) {
                return $value;
            }
            return Carbon::parse($value);
        } catch (InvalidFormatException $e) {
            throw new CastingException('Invalid datetime format: ' . $e->getMessage());
        } catch (TypeError $e) {
            throw new CastingException('Invalid date format: ' . $e->getMessage());
        }
    }
}
