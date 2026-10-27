<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor;

use Derafu\DataProcessor\Contract\ProcessorInterface;
use Derafu\DataProcessor\Contract\RuleParserInterface;
use Derafu\DataProcessor\Contract\RuleRegistrarInterface;
use Derafu\DataProcessor\Contract\RuleRegistryInterface;
use Derafu\DataProcessor\Contract\RuleResolverInterface;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Processor;
use Derafu\DataProcessor\Registrar\CompositeRuleRegistrar;
use Derafu\DataProcessor\Rule\Validator\String\MinLengthRule;
use Derafu\DataProcessor\RuleParser;
use Derafu\DataProcessor\RuleRegistry;
use Derafu\DataProcessor\RuleResolver;
use Derafu\DataProcessor\Translation\DataProcessorTranslationResourceProvider;
use Derafu\TestsDataProcessor\Registrar\AlwaysRule;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Imports the package configuration, compiles a real container and exercises
 * its services. Public visibility is enabled only to prevent unused private
 * services from disappearing before their construction can be checked.
 */
#[CoversNothing]
final class ServicesTest extends TestCase
{
    private function loadContainer(?string $consumerConfiguration = null): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $configuration = $consumerConfiguration === null
            ? dirname(__DIR__, 2) . '/resources/config/data-processor-services.yaml'
            : dirname(__DIR__) . '/fixtures/config/' . $consumerConfiguration;
        (new YamlFileLoader($container, new FileLocator()))->load($configuration);

        return $container;
    }

    private function compile(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            $definition->setPublic(true);
        }
        foreach ($container->getAliases() as $alias) {
            $alias->setPublic(true);
        }
        $container->compile();
    }

    public function testEveryConfiguredServiceCanBeConstructed(): void
    {
        $container = $this->loadContainer();
        $this->compile($container);

        foreach ([
            ProcessorInterface::class => Processor::class,
            RuleResolverInterface::class => RuleResolver::class,
            RuleRegistryInterface::class => RuleRegistry::class,
            RuleParserInterface::class => RuleParser::class,
            RuleRegistrarInterface::class => CompositeRuleRegistrar::class,
            DataProcessorTranslationResourceProvider::class => DataProcessorTranslationResourceProvider::class,
        ] as $id => $class) {
            self::assertInstanceOf($class, $container->get($id));
        }
    }

    public function testTheResolverUsesTheConfiguredRegistry(): void
    {
        $container = $this->loadContainer();
        $this->compile($container);
        $registry = $container->get(RuleRegistryInterface::class);
        self::assertInstanceOf(RuleRegistryInterface::class, $registry);
        $registry->addValidatorRule('custom', AlwaysRule::class);
        $resolver = $container->get(RuleResolverInterface::class);
        self::assertInstanceOf(RuleResolverInterface::class, $resolver);
        [$rule, $parameters] = $resolver->resolve('validate', 'custom:fail');
        self::assertSame($registry->getValidatorRule('custom'), $rule);
        self::assertSame(['fail'], $parameters);
        $processor = $container->get(ProcessorInterface::class);
        self::assertInstanceOf(ProcessorInterface::class, $processor);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Always fails.');
        $processor->process('abc', ['validate' => ['custom:fail']]);
    }

    public function testTheParserAndProcessorApplyTheDefaultRules(): void
    {
        $container = $this->loadContainer();
        $this->compile($container);
        $parser = $container->get(RuleParserInterface::class);
        self::assertInstanceOf(RuleParserInterface::class, $parser);
        $rules = 't(uppercase) v(required|min_length:3)';
        $processor = $container->get(ProcessorInterface::class);
        self::assertInstanceOf(ProcessorInterface::class, $processor);
        self::assertSame('ABC', $processor->process('abc', $rules));
        self::assertSame(42, $processor->process('42', ['cast' => 'integer']));

        $this->expectException(ValidationException::class);
        $processor->process('ab', $rules);
    }

    public function testTaggedRegistrarsExtendTheProcessorWithoutReplacingTheDefaults(): void
    {
        $container = $this->loadContainer('custom-registrar.yaml');
        $this->compile($container);
        $processor = $container->get(ProcessorInterface::class);
        self::assertInstanceOf(ProcessorInterface::class, $processor);
        self::assertSame('ABC', $processor->process('abc', [
            'transform' => ['uppercase'],
            'validate' => ['min_length:3', 'custom'],
        ]));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Always fails.');
        $processor->process('abc', ['validate' => ['custom:fail']]);
    }

    public function testTheProcessorUsesTheParserOverriddenByTheConsumer(): void
    {
        $container = $this->loadContainer('custom-parser.yaml');
        $this->compile($container);
        $processor = $container->get(ProcessorInterface::class);
        self::assertInstanceOf(ProcessorInterface::class, $processor);
        self::assertSame('test@example.com', $processor->process(' TEST@EXAMPLE.COM ', 'contact_email'));

        $this->expectException(ValidationException::class);
        $processor->process('invalid-email', 'contact_email');
    }

    public function testTheProcessorUsesTheResolverOverriddenByTheConsumer(): void
    {
        $container = $this->loadContainer('custom-resolver.yaml');
        $this->compile($container);
        $processor = $container->get(ProcessorInterface::class);
        self::assertInstanceOf(ProcessorInterface::class, $processor);
        self::assertSame('ABC', $processor->process('abc', [
            'transform' => ['uppercase'],
            'validate' => ['min_length=3'],
        ]));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Must be at least 3 characters long.');
        $processor->process('ab', ['validate' => ['min_length=3']]);
    }

    public function testTaggedRegistrarsCanReplaceADefaultRuleAndPreserveOtherDefaults(): void
    {
        $container = $this->loadContainer('override-rule.yaml');
        $this->compile($container);
        $processor = $container->get(ProcessorInterface::class);
        self::assertInstanceOf(ProcessorInterface::class, $processor);
        self::assertSame('AB', $processor->process('ab', [
            'transform' => ['uppercase'],
            'validate' => ['required', 'min_length:100'],
        ]));
        self::assertSame(42, $processor->process('42', ['cast' => 'integer']));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Always fails.');
        $processor->process('abc', ['validate' => ['min_length:fail']]);
    }

    public function testTaggedTranslationResourcesAreUsableByTheConsumer(): void
    {
        $container = $this->loadContainer('translation.yaml');
        self::assertArrayHasKey(
            DataProcessorTranslationResourceProvider::class,
            $container->findTaggedServiceIds('derafu_translation.resource_provider')
        );
        $this->compile($container);
        $translator = $container->get(TranslatorInterface::class);
        self::assertInstanceOf(Translator::class, $translator);
        $rule = new MinLengthRule();
        try {
            $rule->validate('ab', ['3']);
            self::fail('A value below the minimum length was accepted.');
        } catch (ValidationException $exception) {
            self::assertSame('Debe tener al menos 3 caracteres.', $exception->trans($translator));
        }
    }
}
