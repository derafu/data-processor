<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Rule\Validator\String;

use Derafu\DataProcessor\Contract\RawParameterInterface;
use Derafu\DataProcessor\Exception\ValidationException;

final class NotRegexRule extends AbstractRegexRule implements RawParameterInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        if (!is_string($value)) {
            throw new ValidationException('Value must be a string.');
        }

        if (empty($parameters)) {
            throw new ValidationException('Regular expression pattern must be specified.');
        }

        if ($this->matches($parameters[0], $value)) {
            throw new ValidationException('Value matches a pattern it should not match.');
        }
    }
}
