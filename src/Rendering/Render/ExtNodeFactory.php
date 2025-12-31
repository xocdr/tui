<?php

declare(strict_types=1);

namespace Xocdr\Tui\Rendering\Render;

use Xocdr\Tui\Contracts\ExtNodeFactoryInterface;

/**
 * Factory for creating native Ext extension objects.
 *
 * This is the single point of coupling to the C extension for raw Ext object
 * creation, making it easy to swap for testing or alternative backends.
 *
 * @see ExtNodeFactoryInterface
 */
class ExtNodeFactory implements ExtNodeFactoryInterface
{
    /**
     * @param array<string, mixed> $style
     */
    public function createBox(array $style = []): \Xocdr\Tui\Ext\Box
    {
        return new \Xocdr\Tui\Ext\Box($style);
    }

    /**
     * @param array<string, mixed> $style
     */
    public function createText(string $content, array $style = []): \Xocdr\Tui\Ext\Text
    {
        return new \Xocdr\Tui\Ext\Text($content, $style);
    }

    public function createNewline(int $count = 1): \Xocdr\Tui\Ext\Newline
    {
        return new \Xocdr\Tui\Ext\Newline(['count' => $count]);
    }

    public function createSpacer(): object
    {
        // Use native Spacer class if available (ext-tui 0.1.3+)
        if (class_exists(\Xocdr\Tui\Ext\Spacer::class)) {
            return new \Xocdr\Tui\Ext\Spacer();
        }

        // Fallback for older ext-tui versions
        return new \Xocdr\Tui\Ext\Box(['flexGrow' => 1]);
    }
}
