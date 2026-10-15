<?php

declare(strict_types=1);

/**
 * Derafu: Data Processor - Four-Phase Data Processing Library.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsDataProcessor\Exception;

use Closure;
use Derafu\DataProcessor\Exception\CastingException;
use Derafu\DataProcessor\Exception\ProcessorException;
use Derafu\DataProcessor\Exception\RuleNotFoundException;
use Derafu\DataProcessor\Exception\SanitizationException;
use Derafu\DataProcessor\Exception\TransformationException;
use Derafu\DataProcessor\Exception\ValidationException;
use Derafu\DataProcessor\Rule\Validator\Internet\IpRule;
use Derafu\DataProcessor\RuleRegistry;
use Derafu\DataProcessor\RuleResolver;
use Derafu\DataProcessor\Translation\DataProcessorTranslationResourceProvider;
use Derafu\Translation\Contract\TranslatableInterface;
use Derafu\Translation\TranslatorFactory;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

/**
 * Every exception of the package is translatable, and what raises them says
 * what it said before.
 */
#[CoversClass(ProcessorException::class)]
#[CoversClass(RuleNotFoundException::class)]
#[CoversClass(CastingException::class)]
#[CoversClass(SanitizationException::class)]
#[CoversClass(TransformationException::class)]
#[CoversClass(ValidationException::class)]
#[CoversClass(RuleRegistry::class)]
#[CoversClass(RuleResolver::class)]
#[CoversClass(IpRule::class)]
#[CoversClass(DataProcessorTranslationResourceProvider::class)]
final class ProcessorExceptionsTest extends TestCase
{
    /**
     * @return array<string, array{Closure(string|array<mixed>|TranslatableInterface): ProcessorException}>
     */
    public static function exceptionsProvider(): array
    {
        return [
            'processor' => [fn (string|array|TranslatableInterface $message) => new ProcessorException($message)],
            'casting' => [fn (string|array|TranslatableInterface $message) => new CastingException($message)],
            'rule not found' => [fn (string|array|TranslatableInterface $message) => new RuleNotFoundException($message)],
            'sanitization' => [fn (string|array|TranslatableInterface $message) => new SanitizationException($message)],
            'transformation' => [fn (string|array|TranslatableInterface $message) => new TransformationException($message)],
            'validation' => [fn (string|array|TranslatableInterface $message) => new ValidationException($message)],
        ];
    }

    /**
     * @param Closure(string|array<mixed>|TranslatableInterface): ProcessorException $create
     */
    #[DataProvider('exceptionsProvider')]
    public function testEveryExceptionIsTranslatable(Closure $create): void
    {
        $exception = $create(['Failed because {reason}.', 'reason' => 'it is late']);

        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertInstanceOf(TranslatableInterface::class, $exception);
        $this->assertSame('Failed because it is late.', $exception->getMessage());
    }

    /**
     * The exceptions inherit the constructor of ProcessorException. Declaring
     * the trait again in a subclass made the parent constructor rebuild the
     * message from the formatted text, losing the id and the parameters, so
     * only the messages without parameters were translated.
     *
     * @param Closure(string|array<mixed>|TranslatableInterface): ProcessorException $create
     */
    #[DataProvider('exceptionsProvider')]
    public function testTheTranslationKeepsTheIdAndTheParameters(Closure $create): void
    {
        $exception = $create(['Must be at least {min} characters long.', 'min' => 3]);

        $message = $exception->getTranslatableMessage();
        $this->assertSame('Must be at least {min} characters long.', $message->getMessage());
        $this->assertSame(['min' => 3], $message->getParameters());

        $translator = TranslatorFactory::create(
            'es',
            ['en'],
            [new DataProcessorTranslationResourceProvider()]
        );
        $this->assertSame('Debe tener al menos 3 caracteres.', $exception->trans($translator));
    }

    /**
     * @return array<string, array{Closure, class-string<Throwable>, string}>
     */
    public static function failuresProvider(): array
    {
        return [
            'transformer rule' => [fn () => (new RuleRegistry())->getTransformerRule('nope'), RuleNotFoundException::class, 'Transformer rule "nope" not found. Available rules: .'],
            'caster rule' => [fn () => (new RuleRegistry())->getCasterRule('nope'), RuleNotFoundException::class, 'Caster rule "nope" not found. Available rules: .'],
            'sanitizer rule' => [fn () => (new RuleRegistry())->getSanitizerRule('nope'), RuleNotFoundException::class, 'Sanitizer rule "nope" not found. Available rules: .'],
            'validator rule' => [fn () => (new RuleRegistry())->getValidatorRule('nope'), RuleNotFoundException::class, 'Validator rule "nope" not found. Available rules: .'],
            'rule type' => [fn () => (new RuleResolver(new RuleRegistry()))->resolve('other', 'min:1'), InvalidArgumentException::class, 'Invalid rule type: other'],
            'ip version' => [fn () => (new IpRule())->validate('127.0.0.1', ['v5']), ValidationException::class, 'Invalid IP version specified.'],
            'ip format' => [fn () => (new IpRule())->validate('not an ip'), ValidationException::class, 'Invalid IP format.'],
            'ip format with version' => [fn () => (new IpRule())->validate('not an ip', ['v4']), ValidationException::class, 'Invalid IPvv4 format.'],
        ];
    }

    /**
     * @param class-string<Throwable> $class
     */
    #[DataProvider('failuresProvider')]
    public function testTheFailureIsATranslatableErrorThatSaysTheSame(Closure $failure, string $class, string $message): void
    {
        $exception = null;
        try {
            $failure();
        } catch (Throwable $e) {
            $exception = $e;
        }

        $this->assertInstanceOf($class, $exception);
        $this->assertInstanceOf(TranslatableInterface::class, $exception);
        $this->assertSame($message, $exception->getMessage());
    }
}
