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

interface RuleRegistrarInterface
{
    /**
     * Registers all default rules into the provided registry.
     *
     * @param RuleRegistryInterface $registry
     * @return void
     */
    public function register(RuleRegistryInterface $registry): void;
}
