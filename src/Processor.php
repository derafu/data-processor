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

use Derafu\DataProcessor\Contract\ProcessorInterface;
use Derafu\DataProcessor\Contract\RuleParserInterface;
use Derafu\DataProcessor\Contract\RuleResolverInterface;
use Derafu\DataProcessor\Support\Value;

/**
 * Main processor that applies rules in order.
 *
 *     cast -> transform -> sanitize -> validate
 */
final class Processor implements ProcessorInterface
{
    public function __construct(
        private readonly RuleResolverInterface $resolver,
        private readonly RuleParserInterface $parser
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function process(mixed $value, string|array|RuleSet $rules): mixed
    {
        // Parse rules to ensure a set of rules in the correct format.
        $set = $rules instanceof RuleSet ? $rules : RuleSet::fromArray($this->parser->parse($rules));

        // First, apply caster rule (only one allowed).
        if ($set->cast !== null) {
            [$rule, $parameters] = $this->resolver->resolve('cast', $set->cast);
            $value = $rule->cast($value, $parameters);
        }

        // Apply transformer rules.
        foreach ($set->transform as $ruleString) {
            [$rule, $parameters] = $this->resolver->resolve('transform', $ruleString);
            $value = $rule->transform($value, $parameters);
        }

        // Apply sanitizer rules.
        foreach ($set->sanitize as $ruleString) {
            [$rule, $parameters] = $this->resolver->resolve('sanitize', $ruleString);
            $value = $rule->sanitize($value, $parameters);
        }

        // Finally apply validator rules.
        $validationRules = $set->validate;

        // Check if nullable rule is present to determine if empty values should be allowed.
        $hasRequiredRule = $this->hasRequiredRule($validationRules);

        foreach ($validationRules as $ruleString) {
            [$rule, $parameters] = $this->resolver->resolve('validate', $ruleString);

            // If not required rules is enabled and value is empty, skip
            // all the rules except the required rule.
            if (!$hasRequiredRule && Value::isEmpty($value) && !$this->isRequiredRule($ruleString)) {
                continue;
            }

            $rule->validate($value, $parameters);
        }

        return $value;
    }

    /**
     * Checks if the validation rules contain a required rule.
     *
     * @param list<string> $validationRules The validation rules to check.
     * @return bool True if required rule is present, false otherwise.
     */
    private function hasRequiredRule(array $validationRules): bool
    {
        foreach ($validationRules as $ruleString) {
            if (str_starts_with($ruleString, 'required')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if a rule string represents a required rule.
     *
     * @param string $ruleString The rule string to check.
     * @return bool True if it's a required rule, false otherwise.
     */
    private function isRequiredRule(string $ruleString): bool
    {
        return str_starts_with($ruleString, 'required');
    }
}
