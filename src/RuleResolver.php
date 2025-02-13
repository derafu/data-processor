<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor;

use Derafu\DataProcessor\Contract\RuleRegistryInterface;
use Derafu\DataProcessor\Contract\RuleResolverInterface;
use InvalidArgumentException;

/**
 * Resolves rule strings into rule instances with parameters.
 */
final class RuleResolver implements RuleResolverInterface
{
    public function __construct(
        private readonly RuleRegistryInterface $registry
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(string $type, string $ruleString): array
    {
        // Parse rule string (e.g., "min:5" into ["min", ["5"]]).
        $parts = explode(':', $ruleString, 2);
        $ruleName = $parts[0];
        $parameters = isset($parts[1]) ? explode(',', $parts[1]) : [];

        // Get appropriate rule instance.
        $rule = match($type) {
            'transform' => $this->registry->getTransformerRule($ruleName),
            'cast' => $this->registry->getCasterRule($ruleName),
            'sanitize' => $this->registry->getSanitizerRule($ruleName),
            'validate' => $this->registry->getValidatorRule($ruleName),
            default => throw new InvalidArgumentException("Invalid rule type: {$type}")
        };

        return [$rule, $parameters];
    }
}
