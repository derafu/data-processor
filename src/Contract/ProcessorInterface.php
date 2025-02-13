<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Contract;

interface ProcessorInterface
{
    /**
     * Processes a value through all specified rules.
     *
     * @param mixed $value The value to process.
     * @param string|array $rules The rules to apply.
     * @return mixed The processed value.
     */
    public function process(mixed $value, string|array $rules): mixed;
}
