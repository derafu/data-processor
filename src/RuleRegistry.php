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
     * @var array<class-string<TransformerRuleInterface>,TransformerRuleInterface>
     */
    private array $transformerInstances = [];

    /**
     * @var array<class-string<SanitizerRuleInterface>,SanitizerRuleInterface>
     */
    private array $sanitizerInstances = [];

    /**
     * @var array<class-string<CasterRuleInterface>,CasterRuleInterface>
     */
    private array $casterInstances = [];

    /**
     * @var array<class-string<ValidatorRuleInterface>,ValidatorRuleInterface>
     */
    private array $validatorInstances = [];

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
            throw new RuleNotFoundException([
                'Transformer rule "{name}" not found. Available rules: {rules}.',
                'name' => $name,
                'rules' => implode(', ', array_keys($this->transformerRules)),
            ]);
        }

        $class = $this->transformerRules[$name];

        return $this->transformerInstances[$class] ??= new $class();
    }

    /**
     * {@inheritDoc}
     */
    public function getSanitizerRule(string $name): SanitizerRuleInterface
    {
        if (!isset($this->sanitizerRules[$name])) {
            throw new RuleNotFoundException([
                'Sanitizer rule "{name}" not found. Available rules: {rules}.',
                'name' => $name,
                'rules' => implode(', ', array_keys($this->sanitizerRules)),
            ]);
        }

        $class = $this->sanitizerRules[$name];

        return $this->sanitizerInstances[$class] ??= new $class();
    }

    /**
     * {@inheritDoc}
     */
    public function getCasterRule(string $name): CasterRuleInterface
    {
        if (!isset($this->casterRules[$name])) {
            throw new RuleNotFoundException([
                'Caster rule "{name}" not found. Available rules: {rules}.',
                'name' => $name,
                'rules' => implode(', ', array_keys($this->casterRules)),
            ]);
        }

        $class = $this->casterRules[$name];

        return $this->casterInstances[$class] ??= new $class();
    }

    /**
     * {@inheritDoc}
     */
    public function getValidatorRule(string $name): ValidatorRuleInterface
    {
        if (!isset($this->validatorRules[$name])) {
            throw new RuleNotFoundException([
                'Validator rule "{name}" not found. Available rules: {rules}.',
                'name' => $name,
                'rules' => implode(', ', array_keys($this->validatorRules)),
            ]);
        }

        $class = $this->validatorRules[$name];

        return $this->validatorInstances[$class] ??= new $class();
    }
}
