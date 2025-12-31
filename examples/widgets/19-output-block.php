#!/usr/bin/env php
<?php

/**
 * OutputBlock Widget - Command Output Display
 *
 * Demonstrates:
 * - Command output formatting
 * - Error vs success output
 * - Styled terminal output
 *
 * Run in your terminal: php examples/widgets/19-output-block.php
 * Press 'q' or ESC to exit
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Newline;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\UI;
use Xocdr\Tui\Widgets\Content\OutputBlock;

class OutputBlockDemo extends UI
{
    public function build(): Component
    {
        $this->onKeyPress(function ($input, $key) {
            if ($input === 'q' || $key->escape) {
                $this->exit();
            }
        });

        $commandOutput = <<<'OUTPUT'
PHPUnit 10.5.0 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.3.0
Configuration: phpunit.xml

...............................................................  63 / 127 ( 49%)
...............................................................  126 / 127 ( 99%)
.                                                               127 / 127 (100%)

Time: 00:01.234, Memory: 24.00 MB

OK (127 tests, 342 assertions)
OUTPUT;

        $errorOutput = <<<'OUTPUT'
Error: Class 'App\Service\UserService' not found
  at app/Http/Controllers/UserController.php:15

Stack trace:
  #0 vendor/laravel/framework/src/Router.php(123): dispatch()
  #1 public/index.php(55): handle()
OUTPUT;

        return new BoxColumn([
            (new Text('OutputBlock Widget Examples'))->bold(),
            new Newline(),

            (new Text('Test Output:'))->dim(),
            OutputBlock::create($commandOutput),
            new Newline(),

            (new Text('Error Output:'))->dim(),
            OutputBlock::create($errorOutput)->type('stderr'),
            new Newline(),

            (new Text('Command with Header:'))->dim(),
            OutputBlock::create('Installing dependencies...')->command('composer install --no-dev'),
            new Newline(),

            (new Text('Press ESC to exit'))->dim(),
        ]);
    }
}

(new OutputBlockDemo())->run();
