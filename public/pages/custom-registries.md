# Creating Custom Registries

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

// Create resolver, parser and processor with your registry.
$resolver = new RuleResolver($registry);
$parser = new RuleParser();
$processor = new Processor($resolver, $parser);
```
