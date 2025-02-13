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

use InvalidArgumentException;

interface RuleResolverInterface
{
    /**
     * Resolves a rule string into a rule instance and parameters.
     *
     * @param string $type
     * @param string $ruleString
     * @return array{0: object, 1: array} Rule instance and parameters.
     * @throws InvalidArgumentException When type is invalid.
     */
    public function resolve(string $type, string $ruleString): array;
}
