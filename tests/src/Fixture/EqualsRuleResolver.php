<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Fixture;

use Derafu\DataProcessor\Contract\RuleRegistryInterface;
use Derafu\DataProcessor\Contract\RuleResolverInterface;
use Derafu\DataProcessor\RuleResolver;

/**
 * Accepts an equals sign before rule parameters, preserving normal resolution.
 */
final class EqualsRuleResolver implements RuleResolverInterface
{
    public function __construct(private readonly RuleRegistryInterface $registry)
    {
    }

    public function resolve(string $type, string $ruleString): array
    {
        $parts = explode('=', $ruleString, 2);
        $rule = count($parts) === 2 ? $parts[0] . ':' . $parts[1] : $ruleString;

        return (new RuleResolver($this->registry))->resolve($type, $rule);
    }
}
