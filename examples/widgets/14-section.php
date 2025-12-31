#!/usr/bin/env php
<?php

/**
 * Section Widget - Content Sections with Headings
 *
 * Demonstrates:
 * - Major and minor sections
 * - Section levels (H1, H2, H3)
 * - Dividers and icons
 *
 * Run in your terminal: php examples/widgets/14-section.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Layout\Section;

class SectionDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        return new BoxColumn([
            (new Text('Section Widget Examples'))->bold(),
            new Newline(),

            Section::major('Getting Started')
                ->children([
                    new Text('Welcome to the documentation.'),
                    new Text('Follow the steps below to begin.'),
                ]),
            new Newline(),

            Section::create('Installation')
                ->children([
                    new Text('composer require exocoder/tui-widgets'),
                ]),
            new Newline(),

            Section::sub('Prerequisites')
                ->children([
                    new Text('- PHP 8.1 or higher'),
                    new Text('- Composer installed'),
                ]),
            new Newline(),

            Section::create('Configuration')
                ->icon('⚙️')
                ->children([
                    new Text('Configure your settings in config.php'),
                ]),
            new Newline(),

            (new Text('Press ESC to exit'))->dim(),
        ]);
    }
}

(new SectionDemo())->run();
