<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

/**
 * Alias for Static_ component.
 *
 * Provides a cleaner name without the underscore that avoids
 * PHP's reserved word conflict with 'static'.
 *
 * @see Static_
 */
class_alias(Static_::class, StaticOutput::class);
