<?php

declare(strict_types=1);

namespace Xocdr\Tui\Contracts;

/**
 * Factory interface for creating native Ext extension objects.
 *
 * This abstraction allows components to create TuiBox, TuiText, and TuiNewline
 * objects without direct coupling to the C extension, enabling testing with
 * mock implementations.
 */
interface ExtNodeFactoryInterface
{
    /**
     * Create a native Box object.
     *
     * @param array<string, mixed> $style Layout and styling properties
     * @return \Xocdr\Tui\Ext\Box
     */
    public function createBox(array $style = []): object;

    /**
     * Create a native Text object.
     *
     * @param string $content Text content to display
     * @param array<string, mixed> $style Text styling properties
     * @return \Xocdr\Tui\Ext\Text
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
     * Create a native Spacer object (or Box fallback for older ext-tui).
     *
     * @return \Xocdr\Tui\Ext\Spacer|\Xocdr\Tui\Ext\Box
     */
    public function createSpacer(): object;
}
