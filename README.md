# Derafu: Data Processor - Four-Phase Data Processing Library

[![CI Workflow](https://github.com/derafu/data-processor/actions/workflows/ci.yml/badge.svg?branch=main&event=push)](https://github.com/derafu/data-processor/actions/workflows/ci.yml?query=branch%3Amain)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://opensource.org/licenses/MIT)

A PHP library designed to process data through four distinct phases: casting, transformation, sanitization and validation.

## What Makes it Unique?

Unlike traditional validation libraries that focus solely on validation, Derafu Data Processor offers:

- **Clear Phase Separation**: Each processing phase (cast → transform → sanitize → validate) has its own purpose and runs in a specific order.
- **Independent Transformations**: A dedicated transformation layer for complex data conversions, separate from basic type casting.
- **Type-Safe Operations**: Strong typing and clear error handling in each phase.
- **Extensible Rule System**: Each type of rule (caster, transformer, sanitizer, validator) follows its own interface.
- **Almost Zero Dependencies**: just `intl` extension for specific format rules and `Carbon` for dates.

## Features

- 🎯 **Type-Safe Casting**: Convert between data types safely.
  - Basic types (string, int, float, bool).
  - Date and time handling.

- 🔄 **Data Transformations**: Complex data conversions.
  - Base64 encoding/decoding.
  - JSON processing.
  - Slug generation.
  - Character transliteration.

- 🧹 **Rich Sanitization Rules**: Clean and normalize input data.
  - String sanitization (trim, strip_tags, substring, etc.).
  - Regex-based cleaning (regex_remove, regex_keep).
  - HTML entity handling (htmlspecialchars, htmlentities, etc.).
  - Character set manipulation (remove_non_printable, remove_chars, etc.).

- ✅ **Comprehensive Validation**: Various validation rules.
  - String validations (required, min_length, max_length, etc.).
  - Number validations (range, gte, lte, etc.).
  - Date validations (after, before, between, etc.).
  - Format validations (email, URL, UUID, etc.).
  - File validations (file, image, mime_types).
  - Array validations (min_items, max_items, not_empty, etc.).

## Installation

```bash
composer require derafu/data-processor
```

## Basic Usage

```php
use Derafu\DataProcessor\ProcessorFactory;

$processor = ProcessorFactory::create();

// Simple validation.
$result = $processor->process('test@example.com', [
    'validate' => ['email'],
]);

// Multi-phase processing.
$result = $processor->process(' TEST@EXAMPLE.COM ', [
    'transform' => ['lowercase'],
    'sanitize' => ['trim'],
    'validate' => ['email', 'max_length:255'],
]);

// Array validation.
$result = $processor->process([1, 2, 2, 3], [
    'validate' => ['unique', 'max_items:5'],
]);

// File validation.
$result = $processor->process($_FILES['upload'], [
    'validate' => ['file:2M', 'mime_types:application/pdf,image/jpeg']
]);
```

## Creating Custom Rules

### Custom Transform Rule

```php
use Derafu\DataProcessor\Contract\TransformerRuleInterface;

final class CustomTransformRule implements TransformerRuleInterface
{
    public function transform(mixed $value, array $parameters = []): mixed
    {
        // Your transformation logic here.
    }
}

// Register the rule.
$registry->addTransformerRule('custom_transform', CustomTransformRule::class);
```

### Custom Sanitizer Rule

```php
use Derafu\DataProcessor\Contract\SanitizerRuleInterface;

final class CustomSanitizerRule implements SanitizerRuleInterface
{
    public function sanitize(mixed $value, array $parameters = []): mixed
    {
        // Your sanitization logic here.
    }
}

// Register the rule.
$registry->addSanitizerRule('custom_sanitize', CustomSanitizerRule::class);
```

### Custom Cast Rule

```php
use Derafu\DataProcessor\Contract\CasterRuleInterface;

final class CustomCastRule implements CasterRuleInterface
{
    public function cast(mixed $value, array $parameters = []): mixed
    {
        // Your casting logic here.
    }
}

// Register the rule.
$registry->addCasterRule('custom_cast', CustomCastRule::class);
```

### Custom Validator Rule

```php
use Derafu\DataProcessor\Contract\ValidatorRuleInterface;

final class CustomValidatorRule implements ValidatorRuleInterface
{
    public function validate(mixed $value, array $parameters = []): void
    {
        // Your validation logic here.
    }
}

// Register the rule.
$registry->addValidatorRule('custom_validate', CustomValidatorRule::class);
```

### Using Custom Rules

Once registered, you can use your custom rules just like built-in ones:

```php
$processor->process('field', $value, [
    'transform' => ['custom_transform'],
    'sanitize' => ['custom_sanitize'],
    'cast' => 'custom_cast',
    'validate' => ['custom_validate']
]);
```

## Creating Custom Registries

You can create your own rule registry to customize which rules are available:

```php
use Derafu\DataProcessor\RuleRegistry;
use Derafu\DataProcessor\Processor;
use Derafu\DataProcessor\RuleResolver;

// Create a custom registry.
$registry = new RuleRegistry();

// Register only the rules you need.
$registry
    ->addTransformRule('base64_encode', Base64EncodeRule::class)
    ->addSanitizerRule('trim', TrimRule::class)
    ->addCasterRule('integer', IntegerRule::class)
    ->addValidatorRule('email', EmailRule::class);

// Create resolver and processor with your registry.
$resolver = new RuleResolver($registry);
$processor = new Processor($resolver);
```

## Creating Custom Rule Registrar

For better organization, you can create a custom rule registrar:

```php
use Derafu\DataProcessor\Contract\RuleRegistryInterface;

final class CustomRuleRegistrar implements RuleRegistrarInterface
{
    public function register(RuleRegistryInterface $registry): void
    {
        // Register transform rules.
        $registry
            ->addTransformRule('custom_transform_1', CustomTransform1Rule::class)
            ->addTransformRule('custom_transform_2', CustomTransform2Rule::class);

        // Register sanitizer rules.
        $registry
            ->addSanitizerRule('custom_sanitize_1', CustomSanitize1Rule::class)
            ->addSanitizerRule('custom_sanitize_2', CustomSanitize2Rule::class);

        // Register caster rules.
        $registry
            ->addCasterRule('custom_type_1', CustomType1Rule::class)
            ->addCasterRule('custom_type_2', CustomType2Rule::class);

        // Register validator rules.
        $registry
            ->addValidatorRule('custom_validate_1', CustomValidate1Rule::class)
            ->addValidatorRule('custom_validate_2', CustomValidate2Rule::class);
    }
}

// Usage.
$processor = ProcessorFactory::create(
    new CustomRuleRegistrar(),
    withDefaultRules: false // Create without default rules using the factory.
);
```

This allows you to:

- Create domain-specific rule sets.
- Override default rules with custom implementations.
- Group related rules together.
- Control which rules are available in your application.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This library is licensed under the MIT License. See the `LICENSE` file for more details.
