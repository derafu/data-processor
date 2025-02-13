<?php

declare(strict_types=1);

/**
 * Derafu: Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Contract;

interface SanitizerRuleInterface
{
    /**
     * Sanitizes a value according to the rule.
     *
     * @param mixed $value The value to sanitize.
     * @param array $parameters Optional parameters for the rule.
     * @return mixed The sanitized value.
     */
    public function sanitize(mixed $value, array $parameters = []): mixed;
}
