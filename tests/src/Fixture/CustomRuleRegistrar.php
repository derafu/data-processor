<?php

declare(strict_types=1);

namespace Derafu\TestsDataProcessor\Fixture;

use Derafu\DataProcessor\Contract\RuleRegistrarInterface;
use Derafu\DataProcessor\Contract\RuleRegistryInterface;
use Derafu\TestsDataProcessor\Registrar\AlwaysRule;

final class CustomRuleRegistrar implements RuleRegistrarInterface
{
    public function register(RuleRegistryInterface $registry): void
    {
        $registry->addValidatorRule('custom', AlwaysRule::class);
    }
}
