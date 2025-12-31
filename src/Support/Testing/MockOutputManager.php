<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Testing;

use Xocdr\Tui\Contracts\OutputManagerInterface;

/**
 * Mock output manager for testing.
 */
class MockOutputManager implements OutputManagerInterface
{
    private string $lastOutput = '';

    private ?string $capturedOutput = null;

    public function clear(): void
    {
        $this->lastOutput = '';
    }

    public function getLastOutput(): string
    {
        return $this->lastOutput;
    }

    public function setLastOutput(string $output): void
    {
        $this->lastOutput = $output;
    }

    public function getCapturedOutput(): ?string
    {
        return $this->capturedOutput;
    }

    public function setCapturedOutput(?string $output): void
    {
        $this->capturedOutput = $output;
    }

    public function measureElement(string $id): ?array
    {
        // No element measurement in mock
        return null;
    }
}
