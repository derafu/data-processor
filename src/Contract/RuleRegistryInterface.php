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

use Derafu\DataProcessor\Exception\RuleNotFoundException;

interface RuleRegistryInterface
{
    /**
     * Registers a transformer rule.
     *
     * @param string $name
     * @param class-string<TransformerRuleInterface> $rule
     * @return static
     */
    public function addTransformerRule(string $name, string $rule): static;

    /**
     * Registers a sanitizer rule.
     *
     * @param string $name
     * @param class-string<SanitizerRuleInterface> $rule
     * @return static
     */
    public function addSanitizerRule(string $name, string $rule): static;

    /**
     * Registers a caster rule.
     *
     * @param string $name
     * @param class-string<CasterRuleInterface> $rule
     * @return static
     */
    public function addCasterRule(string $name, string $rule): static;

    /**
     * Registers a validator rule.
     *
     * @param string $name
     * @param class-string<ValidatorRuleInterface> $rule
     * @return static
     */
    public function addValidatorRule(string $name, string $rule): static;

    /**
     * Gets a transformer rule by name.
     *
     * @param string $name
     * @return TransformerRuleInterface
     * @throws RuleNotFoundException
     */
    public function getTransformerRule(string $name): TransformerRuleInterface;

    /**
     * Gets a sanitizer rule by name.
     *
     * @param string $name
     * @return SanitizerRuleInterface
     * @throws RuleNotFoundException
     */
    public function getSanitizerRule(string $name): SanitizerRuleInterface;

    /**
     * Gets a caster rule by name.
     *
     * @param string $name
     * @return CasterRuleInterface
     * @throws RuleNotFoundException
     */
    public function getCasterRule(string $name): CasterRuleInterface;

    /**
     * Gets a validator rule by name.
     *
     * @param string $name
     * @return ValidatorRuleInterface
     * @throws RuleNotFoundException
     */
    public function getValidatorRule(string $name): ValidatorRuleInterface;
}
