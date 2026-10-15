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

use Derafu\DataProcessor\Exception\ProcessorException;
use Derafu\DataProcessor\RuleSet;

interface ProcessorInterface
{
    /**
     * Processes a value through all specified rules.
     *
     * @param mixed $value The value to process.
     * @param string|array<string, string|list<string>>|RuleSet $rules The rules
     * to apply: a string in the format of the rules, an array with the rules by
     * category (see `RuleSet::fromArray()`) or the rules already as a set.
     * @return mixed The processed value.
     * @throws ProcessorException If the rules are not valid.
     */
    public function process(mixed $value, string|array|RuleSet $rules): mixed;
}
