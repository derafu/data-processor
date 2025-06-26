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
use Derafu\DataProcessor\Contract\CasterRuleInterface;
use Derafu\DataProcessor\Exception\CastingException;
use TypeError;

final class TimestampRule implements CasterRuleInterface
{
    public function cast(mixed $value, array $parameters = []): int
    {
        try {
            if ($value === null) {
                throw new InvalidFormatException('null is not valid.');
            }
            if (is_numeric($value)) {
                return (int)$value;
            }
            return Carbon::parse($value)->timestamp;
        } catch (InvalidFormatException $e) {
            throw new CastingException('Invalid timestamp format: ' . $e->getMessage());
        } catch (TypeError $e) {
            throw new CastingException('Invalid timestamp format: ' . $e->getMessage());
        }
    }
}
