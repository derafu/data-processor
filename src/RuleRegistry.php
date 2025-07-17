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

use Derafu\DataProcessor\Contract\CasterRuleInterface;
use Derafu\DataProcessor\Contract\RuleRegistryInterface;
use Derafu\DataProcessor\Contract\SanitizerRuleInterface;
use Derafu\DataProcessor\Contract\TransformerRuleInterface;
use Derafu\DataProcessor\Contract\ValidatorRuleInterface;
use Derafu\DataProcessor\Exception\RuleNotFoundException;

/**
 * Registry for all available rules.
 */
final class RuleRegistry implements RuleRegistryInterface
{
    /**
     * @var array<string,class-string<TransformerRuleInterface>>
     */
    private array $transformerRules = [];

    /**
     * @var array<string,class-string<SanitizerRuleInterface>>
     */
    private array $sanitizerRules = [];

    /**
     * @var array<string,class-string<CasterRuleInterface>>
     */
    private array $casterRules = [];

    /**
     * @var array<string,class-string<ValidatorRuleInterface>>
     */
    private array $validatorRules = [];

    /**
     * @var array<string-class,object>
     */
    private array $lazyRules = [];

    /**
     * {@inheritDoc}
     */
    public function addTransformerRule(string $name, string $rule): static
    {
        $this->transformerRules[$name] = $rule;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addSanitizerRule(string $name, string $rule): static
    {
        $this->sanitizerRules[$name] = $rule;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addCasterRule(string $name, string $rule): static
    {
        $this->casterRules[$name] = $rule;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addValidatorRule(string $name, string $rule): static
    {
        $this->validatorRules[$name] = $rule;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTransformerRule(string $name): TransformerRuleInterface
    {
        if (!isset($this->transformerRules[$name])) {
            throw new RuleNotFoundException(sprintf(
                'Transformer rule "%s" not found. Available rules: %s.',
                $name,
                implode(', ', array_keys($this->transformerRules))
            ));
        }

        if (!isset($this->lazyRules[$this->transformerRules[$name]])) {
            $this->lazyRules[$this->transformerRules[$name]] =
                new $this->transformerRules[$name]()
            ;
        }

        return $this->lazyRules[$this->transformerRules[$name]];
    }

    /**
     * {@inheritDoc}
     */
    public function getSanitizerRule(string $name): SanitizerRuleInterface
    {
        if (!isset($this->sanitizerRules[$name])) {
            throw new RuleNotFoundException(sprintf(
                'Sanitizer rule "%s" not found. Available rules: %s.',
                $name,
                implode(', ', array_keys($this->sanitizerRules))
            ));
        }

        if (!isset($this->lazyRules[$this->sanitizerRules[$name]])) {
            $this->lazyRules[$this->sanitizerRules[$name]] =
                new $this->sanitizerRules[$name]()
            ;
        }

        return $this->lazyRules[$this->sanitizerRules[$name]];
    }

    /**
     * {@inheritDoc}
     */
    public function getCasterRule(string $name): CasterRuleInterface
    {
        if (!isset($this->casterRules[$name])) {
            throw new RuleNotFoundException(sprintf(
                'Caster rule "%s" not found. Available rules: %s.',
                $name,
                implode(', ', array_keys($this->casterRules))
            ));
        }

        if (!isset($this->lazyRules[$this->casterRules[$name]])) {
            $this->lazyRules[$this->casterRules[$name]] =
                new $this->casterRules[$name]()
            ;
        }

        return $this->lazyRules[$this->casterRules[$name]];
    }

    /**
     * {@inheritDoc}
     */
    public function getValidatorRule(string $name): ValidatorRuleInterface
    {
        if (!isset($this->validatorRules[$name])) {
            throw new RuleNotFoundException(sprintf(
                'Validator rule "%s" not found. Available rules: %s.',
                $name,
                implode(', ', array_keys($this->validatorRules))
            ));
        }

        if (!isset($this->lazyRules[$this->validatorRules[$name]])) {
            $this->lazyRules[$this->validatorRules[$name]] =
                new $this->validatorRules[$name]()
            ;
        }

        return $this->lazyRules[$this->validatorRules[$name]];
    }
}
