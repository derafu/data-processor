<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor\Registrar;

use Derafu\DataProcessor\Contract\RuleRegistrarInterface;
use Derafu\DataProcessor\Contract\RuleRegistryInterface;

/**
 * Registers the rules of several registrars, in the order they are given.
 *
 * It lets packages add their own rules (for example, the ones of a country)
 * without the application defining the processor again: each package tags its
 * registrar with `derafu_data_processor.rule_registrar` and the processor of
 * `data-processor-services.yaml` receives all of them.
 *
 * A rule with the name of a previous one replaces it, so the last registrar
 * wins.
 */
final class CompositeRuleRegistrar implements RuleRegistrarInterface
{
    /**
     * @param iterable<RuleRegistrarInterface> $registrars The registrars, in
     * the order they register their rules.
     */
    public function __construct(private readonly iterable $registrars = [])
    {
    }

    /**
     * {@inheritDoc}
     */
    public function register(RuleRegistryInterface $registry): void
    {
        foreach ($this->registrars as $registrar) {
            $registrar->register($registry);
        }
    }
}
