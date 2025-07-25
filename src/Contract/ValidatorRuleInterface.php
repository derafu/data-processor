<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Contract;

use Derafu\DataProcessor\Exception\ValidationException;

interface ValidatorRuleInterface
{
    /**
     * Validates a value according to the rule.
     *
     * @param mixed $value The value to validate.
     * @param array $parameters Optional parameters for validation.
     * @return void
     * @throws ValidationException If validation fails.
     */
    public function validate(mixed $value, array $parameters = []): void;
}
