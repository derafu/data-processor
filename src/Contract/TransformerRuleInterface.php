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

use Derafu\DataProcessor\Exception\TransformationException;

interface TransformerRuleInterface
{
    /**
     * Transforms a value according to the rule.
     *
     * @param mixed $value The value to transform.
     * @param array $parameters Optional parameters for the transformation.
     * @return mixed The transformed value.
     * @throws TransformationException If the transformation fails.
     */
    public function transform(mixed $value, array $parameters = []): mixed;
}
