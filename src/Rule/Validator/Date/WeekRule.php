<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\Date;

use DateTimeImmutable;
use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\ValidationException;

/**
 * Validates a week as `<input type="week">` sends it: `YYYY-Www`.
 *
 * The week goes from `01` to `52`, or to `53` in the years of ISO 8601 that
 * have 53 weeks (as 2026). The year goes from `0001`.
 */
final class WeekRule implements ValidatorRuleInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        if (!preg_match('/^(?!0000)(\d{4})-W(\d{2})$/D', $value, $parts)) {
            throw new ValidationException('Invalid week format.');
        }

        $year = (int) $parts[1];
        $week = (int) $parts[2];

        // The week 53 exists only if its Thursday is still in the same year.
        $last = (new DateTimeImmutable())->setISODate($year, 53);
        $weeks = (int) $last->format('o') === $year ? 53 : 52;

        if ($week < 1 || $week > $weeks) {
            throw new ValidationException('Invalid week format.');
        }
    }
}
