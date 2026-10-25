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
use Derafu\Translation\TranslatorFactory;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

/**
 * Imports the package configuration, compiles a real container and exercises
 * its services. Public visibility is enabled only to prevent unused private
 * services from disappearing before their construction can be checked.
 */
#[CoversNothing]
final class ServicesTest extends TestCase
{
    private function loadContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        (new YamlFileLoader($container, new FileLocator(dirname(__DIR__, 2) . '/resources/config')))
            ->load('data-processor-services.yaml');

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
            CompositeRuleRegistrar::class => CompositeRuleRegistrar::class,
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
    }

    public function testTheParserAndProcessorApplyTheDefaultRules(): void
    {
        $container = $this->loadContainer();
        $this->compile($container);
        $parser = $container->get(RuleParserInterface::class);
        self::assertInstanceOf(RuleParserInterface::class, $parser);
        $rules = $parser->parse('t(uppercase) v(required|min_length:3)');
        $processor = $container->get(ProcessorInterface::class);
        self::assertInstanceOf(ProcessorInterface::class, $processor);
        self::assertSame('ABC', $processor->process('abc', $rules));
        self::assertSame(42, $processor->process('42', ['cast' => 'integer']));

        $this->expectException(ValidationException::class);
        $processor->process('ab', $rules);
    }

    public function testTaggedRegistrarsExtendTheProcessorWithoutReplacingTheDefaults(): void
    {
        $container = $this->loadContainer();
        $registrar = new class () implements RuleRegistrarInterface {
            public function register(RuleRegistryInterface $registry): void
            {
                $registry->addValidatorRule('custom', AlwaysRule::class);
            }
        };
        // An application's registrar is a real service, not a mock.
        $container->register('app.rule_registrar', RuleRegistrarInterface::class)
            ->setSynthetic(true)
            ->addTag('derafu_data_processor.rule_registrar');
        self::assertArrayHasKey('app.rule_registrar', $container->findTaggedServiceIds('derafu_data_processor.rule_registrar'));
        $this->compile($container);
        $container->set('app.rule_registrar', $registrar);
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

    public function testTaggedTranslationResourcesAreUsableByTheConsumer(): void
    {
        $container = $this->loadContainer();
        self::assertArrayHasKey(
            DataProcessorTranslationResourceProvider::class,
            $container->findTaggedServiceIds('derafu_translation.resource_provider')
        );
        // The consumer owns its translator and chooses its locale.
        $container->register('app.translator', Translator::class)
            ->setFactory([TranslatorFactory::class, 'create'])
            ->setArguments(['es', ['en'], new TaggedIteratorArgument('derafu_translation.resource_provider')]);
        $this->compile($container);
        $translator = $container->get('app.translator');
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
