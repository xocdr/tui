<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Factory interface for creating native Ext extension objects.
 *
 * This abstraction allows components to create ContainerNode, ContentNode, and Newline
 * objects without direct coupling to the C extension, enabling testing with
 * mock implementations.
 */
interface ExtNodeFactoryInterface
{
    /**
     * Create a native ContainerNode object.
     *
     * @param array<string, mixed> $style Layout and styling properties
     * @return \Xocdr\Tui\Ext\ContainerNode
     */
    public function createBox(array $style = []): object;

    /**
     * Create a native ContentNode object.
     *
     * @param string $content Text content to display
     * @param array<string, mixed> $style Text styling properties
     * @return \Xocdr\Tui\Ext\ContentNode
     */
    public function createText(string $content, array $style = []): object;

    /**
     * Create a native Newline object.
     *
     * @param int $count Number of newlines
     * @return \Xocdr\Tui\Ext\Newline
     */
    public function createNewline(int $count = 1): object;

    /**
     * Create a native Spacer object (or ContainerNode fallback for older ext-tui).
     *
     * @return \Xocdr\Tui\Ext\Spacer|\Xocdr\Tui\Ext\ContainerNode
     */
    public function createSpacer(): object;
}
