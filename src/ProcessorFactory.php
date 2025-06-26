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

use Closure;
use Derafu\DataProcessor\Contract\ProcessorInterface;
use Derafu\DataProcessor\Contract\RuleParserInterface;
use Derafu\DataProcessor\Contract\RuleRegistrarInterface;
use Derafu\DataProcessor\Registrar\DefaultRuleRegistrar;

/**
 * Factory for creating preconfigured data processors.
 */
final class ProcessorFactory
{
    /**
     * Creates a new processor.
     *
     * By default it will include default rules.
     *
     * @param RuleRegistrarInterface|Closure|null $configurator Function to
     * configure the registry.
     * @param bool $withDefaultRules
     * @return ProcessorInterface
     */
    public static function create(
        RuleRegistrarInterface|Closure|null $configurator = null,
        bool $withDefaultRules = true,
        ?RuleParserInterface $parser = null
    ): ProcessorInterface {
        $registry = new RuleRegistry();

        // Register default rules first.
        if ($withDefaultRules) {
            $registrar = new DefaultRuleRegistrar();
            $registrar->register($registry);
        }

        // Allow custom configuration.
        if ($configurator !== null) {
            if ($configurator instanceof RuleRegistrarInterface) {
                $configurator->register($registry);
            } else {
                $configurator($registry);
            }
        }

        $resolver = new RuleResolver($registry);

        // Use default parser.
        if ($parser === null) {
            $parser = new RuleParser();
        }

        return new Processor($resolver, $parser);
    }

    /**
     * Creates a new processor with default rules.
     *
     * @return ProcessorInterface
     */
    public static function createDefault(): ProcessorInterface
    {
        return self::create();
    }

    /**
     * Creates a new processor with only custom rules.
     *
     * @param RuleRegistrarInterface|Closure $configurator Function to configure
     * the registry.
     * @return ProcessorInterface
     */
    public static function createCustom(
        RuleRegistrarInterface|Closure $configurator
    ): ProcessorInterface {
        return self::create($configurator, withDefaultRules: false);
    }
}
