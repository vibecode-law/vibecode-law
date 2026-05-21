<?php

namespace App\Support\TypeScript;

use ReflectionClass;
use Spatie\TypeScriptTransformer\Collectors\EnumCollector as BaseEnumCollector;

/**
 * Collects backed enums for TypeScript generation, but skips any enum marked
 * with the {@see SkipTypeScriptTransform} attribute.
 */
class EnumCollector extends BaseEnumCollector
{
    protected function shouldCollect(ReflectionClass $class): bool
    {
        if (\count($class->getAttributes(SkipTypeScriptTransform::class)) > 0) {
            return false;
        }

        return parent::shouldCollect($class);
    }
}
