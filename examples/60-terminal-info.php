#!/usr/bin/env php
<?php

/**
 * Terminal Info - Terminal utilities and detection
 *
 * Demonstrates:
 * - Terminal size detection
 * - Interactive mode detection
 * - CI environment detection
 * - stdout hook
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Ext\Color;
use Xocdr\Tui\UI;

class TerminalInfoDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($key->escape) {
                $this->exit();
            }
        });

        // Get terminal info from TerminalManager via runtime
        $terminal = $this->runtime()->getTerminalManager();
        $size = $terminal->getSize();
        $isInteractive = $terminal->isInteractive();
        $isCI = $terminal->isCi();

        // Stdout hook provides similar info
        $stdout = $this->hooks()->stdout();

        return new Box([
            new BoxColumn([
                (new Text('=== Terminal Information ==='))->bold()->color(Color::Cyan),
                new Newline(),

                (new BoxColumn([
                    (new Text('Terminal Size'))->bold(),
                    new Text("  Width:  {$size['width']} columns"),
                    new Text("  Height: {$size['height']} rows"),
                ]))->border('round')->borderColor('#888888')->padding(1),
                new Newline(),

                (new BoxColumn([
                    (new Text('Environment'))->bold(),
                    (new Text('  Interactive: ' . ($isInteractive ? 'Yes' : 'No')))->color($isInteractive ? '#00ff00' : '#ff0000'),
                    (new Text('  CI Mode: ' . ($isCI ? 'Yes' : 'No')))->color($isCI ? '#ffff00' : '#00ff00'),
                ]))->border('round')->borderColor('#888888')->padding(1),
                new Newline(),

                (new BoxColumn([
                    (new Text('Stdout Hook'))->bold()->color(Color::Cyan),
                    new Text("  Columns: {$stdout['columns']}"),
                    new Text("  Rows: {$stdout['rows']}"),
                ]))->border('round')->borderColor('#888888')->padding(1),
                new Newline(),

                (new Text('Press ESC to exit.'))->dim(),
            ]),
        ]);
    }
}

(new TerminalInfoDemo())->run();
