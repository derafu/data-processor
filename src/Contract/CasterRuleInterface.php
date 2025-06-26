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

interface CasterRuleInterface
{
    /**
     * Casts a value to the specified type.
     *
     * @param mixed $value The value to cast.
     * @param array $parameters Optional parameters for casting.
     * @return mixed The casted value.
     */
    public function cast(mixed $value, array $parameters = []): mixed;
}
