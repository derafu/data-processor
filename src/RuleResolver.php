<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\DataProcessor;

use Derafu\DataProcessor\Contract\RawParameterInterface;
use Derafu\DataProcessor\Contract\RuleRegistryInterface;
use Derafu\DataProcessor\Contract\RuleResolverInterface;
use Derafu\Translation\Exception\Logic\TranslatableInvalidArgumentException as InvalidArgumentException;

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

        // Get appropriate rule instance.
        $rule = match($type) {
            'transform' => $this->registry->getTransformerRule($ruleName),
            'cast' => $this->registry->getCasterRule($ruleName),
            'sanitize' => $this->registry->getSanitizerRule($ruleName),
            'validate' => $this->registry->getValidatorRule($ruleName),
            default => throw new InvalidArgumentException(['Invalid rule type: {type}', 'type' => $type])
        };

        // The parameters are split by comma, except for a rule that takes one
        // literal: it gets all of it.
        $parameters = match (true) {
            !isset($parts[1]) => [],
            $rule instanceof RawParameterInterface => [$parts[1]],
            default => $this->splitParameters($parts[1]),
        };

        return [$rule, $parameters];
    }

    /**
     * Splits the parameters by comma. A comma that is part of a parameter is
     * written as `\,`.
     *
     * @param string $parameters The parameters, as they were written.
     * @return list<string> The parameters.
     */
    private function splitParameters(string $parameters): array
    {
        $parts = preg_split('/(?<!\\\\),/', $parameters);

        return array_map(
            static fn (string $part): string => str_replace('\\,', ',', $part),
            $parts !== false ? $parts : [$parameters]
        );
    }
}
