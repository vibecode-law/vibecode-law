<?php

namespace App\Support\TypeScript;

use Attribute;

/**
 * Marks a backed enum (or class) that should be excluded from TypeScript
 * generation, e.g. backend-only enums that the frontend never consumes.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class SkipTypeScriptTransform {}
