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

use Derafu\DataProcessor\Exception\ProcessorException;

/**
 * The rules that are applied to a value, in the phases of the processor.
 *
 * It is the validated and normalized form of the rules in array format, which
 * is what the processor reads: each category is a list of rules, and the cast
 * is one rule. The processor applies them in this order:
 *
 *     cast -> transform -> sanitize -> validate
 */
final readonly class RuleSet
{
    /**
     * The categories of the rules, in the order of their output.
     */
    private const CATEGORIES = ['transform', 'sanitize', 'cast', 'validate'];

    /**
     * @param string|null $cast The rule of the cast (only one is allowed).
     * @param list<string> $transform The rules of the transformers.
     * @param list<string> $sanitize The rules of the sanitizers.
     * @param list<string> $validate The rules of the validators.
     */
    public function __construct(
        public ?string $cast = null,
        public array $transform = [],
        public array $sanitize = [],
        public array $validate = [],
    ) {
    }

    /**
     * Creates the rules from an array, validating it.
     *
     * The keys are `cast` (a string) and `transform`, `sanitize` and `validate`
     * (each one a list of strings, or a string with the rules separated by
     * `|`). A key whose value is `null` is taken as absent. The rules that are
     * given as a list are kept as they are.
     *
     * A category given as a string is split by `|`, so a rule that has a `|` in
     * its parameter (`regex:/a|b/`) must be given in the list. A list is the
     * only way to give it, here and in the string format of `RuleParser`.
     *
     * @param array<array-key, mixed> $rules The rules in array format.
     * @throws ProcessorException If a key is not a category of rules, or its
     * value is not what the category has.
     */
    public static function fromArray(array $rules): self
    {
        foreach (array_keys($rules) as $category) {
            if (!in_array($category, self::CATEGORIES, true)) {
                throw new ProcessorException([
                    'The rules have a category that is not known: {category}. The categories are: {categories}.',
                    'category' => (string) $category,
                    'categories' => 'cast, transform, sanitize, validate',
                ]);
            }
        }

        return new self(
            cast: self::cast($rules['cast'] ?? null),
            transform: self::list('transform', $rules['transform'] ?? null),
            sanitize: self::list('sanitize', $rules['sanitize'] ?? null),
            validate: self::list('validate', $rules['validate'] ?? null),
        );
    }

    /**
     * Returns the rules in array format.
     *
     * Only the categories that have rules are included, in this order:
     * `transform`, `sanitize`, `cast` and `validate`.
     *
     * @return array{transform?: list<string>, sanitize?: list<string>, cast?: string, validate?: list<string>}
     */
    public function toArray(): array
    {
        $rules = [];

        if ($this->transform !== []) {
            $rules['transform'] = $this->transform;
        }

        if ($this->sanitize !== []) {
            $rules['sanitize'] = $this->sanitize;
        }

        if ($this->cast !== null) {
            $rules['cast'] = $this->cast;
        }

        if ($this->validate !== []) {
            $rules['validate'] = $this->validate;
        }

        return $rules;
    }

    /**
     * @throws ProcessorException If the value is not a string.
     */
    private static function cast(mixed $value): ?string
    {
        return match (true) {
            $value === null => null,
            is_string($value) => $value,
            default => throw new ProcessorException([
                'The rule of the category "cast" must be a string, {given} given.',
                'given' => get_debug_type($value),
            ]),
        };
    }

    /**
     * @return list<string>
     * @throws ProcessorException If the value is not a string nor a list of
     * strings.
     */
    private static function list(string $category, mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_string($value)) {
            return explode('|', $value);
        }

        if (is_array($value) && array_is_list($value) && array_filter($value, is_string(...)) === $value) {
            /** @var list<string> $value */
            return $value;
        }

        throw new ProcessorException([
            'The rules of the category "{category}" must be a string or a list of strings, {given} given.',
            'category' => $category,
            'given' => get_debug_type($value),
        ]);
    }
}
