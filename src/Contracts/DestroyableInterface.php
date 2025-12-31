<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Interface for classes that hold native resources requiring explicit cleanup.
 *
 * Classes that wrap ext-tui resources (like virtual lists, smooth scrollers,
 * or images) should implement this interface to ensure proper resource
 * management. While PHP's destructor (__destruct) handles most cases,
 * this interface allows explicit cleanup when needed.
 *
 * @example
 * $list = new VirtualList($items);
 * // ... use the list
 * $list->destroy(); // Explicit cleanup
 *
 * // Or rely on automatic cleanup via destructor:
 * unset($list); // __destruct calls destroy()
 */
interface DestroyableInterface
{
    /**
     * Release any native resources held by this instance.
     *
     * This method should be idempotent - calling it multiple times
     * should have no adverse effects. After calling destroy(), the
     * instance should be considered unusable.
     */
    public function destroy(): void;

    /**
     * Check if this instance has been destroyed.
     *
     * @return bool True if destroy() has been called
     */
    public function isDestroyed(): bool;
}
