#!/usr/bin/env php
<?php

/**
 * Text Utilities - String manipulation for terminals
 *
 * Demonstrates:
 * - Measuring string width (Unicode-aware)
 * - Wrapping text to a width
 * - Truncating with ellipsis
 * - Padding and alignment
 *
 * Run in your terminal: php examples/24-text-utils.php
 * Press ESC to exit.
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Contracts\HooksAwareInterface;
use Xocdr\Tui\Hooks\HooksAwareTrait;
use Xocdr\Tui\Text\TextUtils;
use Xocdr\Tui\Tui;

if (!Tui::isInteractive()) {
    echo "Error: This example requires an interactive terminal.\n";
    exit(1);
}

// Demo strings
$ascii = 'Hello, World!';
$unicode = 'Hello, 世界!';  // Chinese characters are 2 cells wide
$emoji = 'Hello! 👋🌍';

// Width measurements
$widths = [
    [$ascii, TextUtils::width($ascii)],
    [$unicode, TextUtils::width($unicode)],
    [$emoji, TextUtils::width($emoji)],
];

// Text wrapping
$longText = 'The quick brown fox jumps over the lazy dog. Pack my box with five dozen liquor jugs.';
$wrapped = TextUtils::wrap($longText, 30);

// Truncation
$truncated = TextUtils::truncate($longText, 25);

// Padding
$padLeft = TextUtils::left('left', 15);
$padRight = TextUtils::right('right', 15);
$padCenter = TextUtils::center('center', 15);

class TextUtilsDemo implements Component, HooksAwareInterface
{
    use HooksAwareTrait;

    public function __construct(
        private array $widths,
        private array $wrapped,
        private string $truncated,
        private string $padLeft,
        private string $padRight,
        private string $padCenter,
    ) {
    }

    public function render(): mixed
    {
        ['exit' => $exit] = $this->hooks()->app();

        $this->hooks()->onInput(function ($input, $key) use ($exit) {
            if ($key->escape) {
                $exit();
            }
        });

        return Box::column([
            Text::create('Text Utilities Demo')->bold()->cyan(),
            Text::create(''),
            Text::create('String Width (Unicode-aware):')->bold(),
            ...array_map(
                fn ($w) => Text::create(sprintf('  "%s" = %d cells', $w[0], $w[1])),
                $this->widths
            ),
            Text::create(''),
            Text::create('Text Wrapping (30 chars):')->bold(),
            ...array_map(fn ($line) => Text::create('  │' . $line . '│'), $this->wrapped),
            Text::create(''),
            Text::create('Truncation (25 chars):')->bold(),
            Text::create("  \"{$this->truncated}\""),
            Text::create(''),
            Text::create('Padding/Alignment (15 chars):')->bold(),
            Text::create("  Left:   │{$this->padLeft}│"),
            Text::create("  Right:  │{$this->padRight}│"),
            Text::create("  Center: │{$this->padCenter}│"),
            Text::create(''),
            Text::create('Press ESC to exit.')->dim(),
        ]);
    }
}

$instance = Tui::render(new TextUtilsDemo($widths, $wrapped, $truncated, $padLeft, $padRight, $padCenter));
$instance->waitUntilExit();
