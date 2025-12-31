<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Testing;

use Xocdr\Tui\Contracts\TerminalManagerInterface;

/**
 * Mock terminal manager for testing.
 */
class MockTerminalManager implements TerminalManagerInterface
{
    private ?string $currentTitle = null;

    private string $cursorShape = 'default';

    private bool $cursorHidden = false;

    /** @var array{width: int, height: int} */
    private array $size;

    public function __construct(int $width = 80, int $height = 24)
    {
        $this->size = ['width' => $width, 'height' => $height];
    }

    public function getSize(): array
    {
        return $this->size;
    }

    public function setSize(int $width, int $height): void
    {
        $this->size = ['width' => $width, 'height' => $height];
    }

    public function isInteractive(): bool
    {
        return true;
    }

    public function isCi(): bool
    {
        return false;
    }

    public function setTitle(string $title): void
    {
        $this->currentTitle = $title;
    }

    public function resetTitle(): void
    {
        $this->currentTitle = null;
    }

    public function getTitle(): ?string
    {
        return $this->currentTitle;
    }

    public function setCursorShape(string $shape): void
    {
        $this->cursorShape = $shape;
    }

    public function getCursorShape(): string
    {
        return $this->cursorShape;
    }

    public function showCursor(): void
    {
        $this->cursorHidden = false;
    }

    public function hideCursor(): void
    {
        $this->cursorHidden = true;
    }

    public function isCursorHidden(): bool
    {
        return $this->cursorHidden;
    }

    public function getCapabilities(): ?array
    {
        return [
            'terminal' => 'mock',
            'name' => 'MockTerminal',
            'version' => '1.0.0',
            'color_depth' => 16777216,
            'capabilities' => [
                'true_color' => true,
                'mouse' => true,
                'hyperlinks_osc8' => true,
                'sync_output' => true,
            ],
        ];
    }

    public function hasCapability(string $name): bool
    {
        $caps = $this->getCapabilities();

        return $caps['capabilities'][$name] ?? false;
    }

    public function getTerminalType(): ?string
    {
        return 'mock';
    }

    public function getColorDepth(): int
    {
        return 16777216;
    }

    public function supportsTrueColor(): bool
    {
        return true;
    }

    public function supportsHyperlinks(): bool
    {
        return true;
    }

    public function supportsMouse(): bool
    {
        return true;
    }

    public function supportsSyncOutput(): bool
    {
        return true;
    }
}
