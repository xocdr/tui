#!/usr/bin/env php
<?php

/**
 * Checklist Widget - Checkable Item Lists
 *
 * Demonstrates:
 * - Basic checklist with toggle
 * - Pre-checked items
 * - Progress display
 *
 * Run in your terminal: php examples/widgets/08-checklist.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Display\Checklist;

class ChecklistDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $items = [
            ['label' => 'Install dependencies', 'checked' => true],
            ['label' => 'Configure environment', 'checked' => true],
            ['label' => 'Run database migrations', 'checked' => false],
            ['label' => 'Set up caching', 'checked' => false],
            ['label' => 'Deploy to production', 'checked' => false],
        ];

        return new Box([
            new BoxColumn([
                (new Text('Checklist Widget Examples'))->bold(),
                (new Text('Use j/k or arrows to navigate, Space to toggle, a=check all, u=uncheck all'))->dim(),
                new Newline(),

                (new Text('Deployment Checklist (interactive):'))->dim(),
                Checklist::create($items)
                    ->interactive()
                    ->showProgress(true),
                new Newline(),

                (new Text('Review Checklist (readonly):'))->dim(),
                Checklist::create([
                    ['label' => 'Code review completed', 'checked' => true],
                    ['label' => 'Tests passing', 'checked' => true],
                    ['label' => 'Documentation updated', 'checked' => false],
                    ['label' => 'Changelog entry added', 'checked' => false],
                ])->showProgress(true)->readonly(),
                new Newline(),

                (new Text('Press q or ESC to exit'))->dim(),
            ]),
        ]);
    }
}

(new ChecklistDemo())->run();
