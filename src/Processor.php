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

use Derafu\DataProcessor\Contract\ProcessorInterface;
use Derafu\DataProcessor\Contract\RuleParserInterface;
use Derafu\DataProcessor\Contract\RuleResolverInterface;

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
    public function process(mixed $value, string|array $rules): mixed
    {
        // Parse rules to ensure an array in the correct format.
        $rules = $this->parser->parse($rules);

        // First, apply caster rule (only one allowed).
        if (isset($rules['cast'])) {
            [$rule, $parameters] = $this->resolver->resolve('cast', $rules['cast']);
            $value = $rule->cast($value, $parameters);
        }

        // Apply transformer rules.
        if (isset($rules['transform'])) {
            foreach ((array)$rules['transform'] as $ruleString) {
                [$rule, $parameters] = $this->resolver->resolve('transform', $ruleString);
                $value = $rule->transform($value, $parameters);
            }
        }

        // Apply sanitizer rules.
        if (isset($rules['sanitize'])) {
            foreach ((array)$rules['sanitize'] as $ruleString) {
                [$rule, $parameters] = $this->resolver->resolve('sanitize', $ruleString);
                $value = $rule->sanitize($value, $parameters);
            }
        }

        // Finally apply validator rules.
        if (isset($rules['validate'])) {
            foreach ((array)$rules['validate'] as $ruleString) {
                [$rule, $parameters] = $this->resolver->resolve('validate', $ruleString);
                $rule->validate($value, $parameters);
            }
        }

        return $value;
    }
}
