# Examples

Complete examples demonstrating common use cases.

## Interactive Todo Application

A full-featured todo list with add, complete, and delete functionality.

[[TODO:SCREENSHOT:interactive-todo example]]

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Xocdr\Tui\Tui;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Hooks;
use Xocdr\Tui\Widgets\Display\TodoList;
use Xocdr\Tui\Widgets\Display\TodoItem;
use Xocdr\Tui\Widgets\Display\TodoStatus;
use Xocdr\Tui\Widgets\Input\Input;
use Xocdr\Tui\Widgets\Feedback\KeyHint;

$app = function () {
    $hooks = Hooks::current();

    [$todos, $setTodos] = $hooks->state([
        new TodoItem('Learn TUI Widgets', TodoStatus::IN_PROGRESS),
        new TodoItem('Build an app', TodoStatus::PENDING),
    ]);

    [$inputValue, $setInputValue] = $hooks->state('');

    $addTodo = function () use ($inputValue, $setInputValue, $todos, $setTodos) {
        if (trim($inputValue) !== '') {
            $newTodo = new TodoItem($inputValue, TodoStatus::PENDING);
            $setTodos([...$todos, $newTodo]);
            $setInputValue('');
        }
    };

    return Box::column([
        Text::create('📋 My Todo List')->bold(),
        Text::create(''),

        TodoList::create($todos)
            ->interactive(true)
            ->showProgress(true)
            ->onStatusChange(function ($id, $status) use ($todos, $setTodos) {
                // Toggle status on space
            }),

        Text::create(''),

        Input::create()
            ->placeholder('Add new todo...')
            ->value($inputValue)
            ->onChange(fn($v) => $setInputValue($v))
            ->onSubmit(fn($v) => $addTodo()),

        Text::create(''),

        KeyHint::create([
            ['key' => 'Space', 'action' => 'Toggle status'],
            ['key' => 'Enter', 'action' => 'Add todo'],
            ['key' => 'Ctrl+C', 'action' => 'Exit'],
        ]),
    ]);
};

Tui::render($app)->waitUntilExit();
```

---

## File Browser

A tree-based file browser with preview.

[[TODO:SCREENSHOT:file-browser example]]

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Xocdr\Tui\Tui;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Hooks;
use Xocdr\Tui\Widgets\Display\Tree;
use Xocdr\Tui\Widgets\Display\TreeNode;
use Xocdr\Tui\Widgets\Content\ContentBlock;
use Xocdr\Tui\Widgets\Layout\Divider;

$app = function () {
    $hooks = Hooks::current();

    [$selectedFile, $setSelectedFile] = $hooks->state(null);
    [$fileContent, $setFileContent] = $hooks->state('');

    $files = [
        new TreeNode('src', [
            new TreeNode('Components', [
                new TreeNode('Button.php'),
                new TreeNode('Input.php'),
            ]),
            new TreeNode('Widgets', [
                new TreeNode('Alert.php'),
                new TreeNode('Badge.php'),
            ]),
        ]),
        new TreeNode('tests', [
            new TreeNode('ComponentTest.php'),
            new TreeNode('WidgetTest.php'),
        ]),
        new TreeNode('README.md'),
        new TreeNode('composer.json'),
    ];

    return Box::row([
        // File tree panel
        Box::create()
            ->width(30)
            ->border('single')
            ->borderTitle('Files')
            ->children([
                Tree::create($files)
                    ->interactive(true)
                    ->searchable(true)
                    ->pageSize(15)
                    ->onSelect(function ($node) use ($setSelectedFile, $setFileContent) {
                        $setSelectedFile($node->label);
                        // In real app: read file content
                        $setFileContent("// Content of {$node->label}");
                    }),
            ]),

        Divider::create()->vertical()->height(20),

        // Preview panel
        Box::create()
            ->flexGrow(1)
            ->border('single')
            ->borderTitle($selectedFile ?? 'Preview')
            ->children([
                $selectedFile !== null
                    ? ContentBlock::create()
                        ->content($fileContent)
                        ->showLineNumbers(true)
                        ->syntaxHighlight(true)
                        ->language('php')
                    : Text::create('Select a file to preview')->dim(),
            ]),
    ]);
};

Tui::render($app)->waitUntilExit();
```

---

## Form with Validation

A multi-field form with real-time validation.

[[TODO:SCREENSHOT:form-validation example]]

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Xocdr\Tui\Tui;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Input\Form;
use Xocdr\Tui\Widgets\Input\FormField;
use Xocdr\Tui\Widgets\Feedback\Alert;

$app = function () {
    return Box::column([
        Text::create('📝 User Registration')->bold(),
        Text::create(''),

        Form::create()
            ->fields([
                FormField::create('username')
                    ->label('Username')
                    ->required(true)
                    ->validation(function ($value) {
                        if (strlen($value) < 3) {
                            return 'Username must be at least 3 characters';
                        }
                        if (!preg_match('/^[a-z0-9_]+$/', $value)) {
                            return 'Username can only contain lowercase letters, numbers, and underscores';
                        }
                        return null;
                    }),

                FormField::create('email')
                    ->label('Email')
                    ->required(true)
                    ->validation(function ($value) {
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            return 'Please enter a valid email address';
                        }
                        return null;
                    }),

                FormField::create('password')
                    ->label('Password')
                    ->masked(true)
                    ->required(true)
                    ->validation(function ($value) {
                        if (strlen($value) < 8) {
                            return 'Password must be at least 8 characters';
                        }
                        return null;
                    }),

                FormField::create('confirm')
                    ->label('Confirm Password')
                    ->masked(true)
                    ->required(true),
            ])
            ->onSubmit(function ($values) {
                if ($values['password'] !== $values['confirm']) {
                    // Show error
                    return;
                }
                // Process registration
            }),

        Text::create(''),
        Text::create('Tab to navigate, Enter to submit')->dim(),
    ]);
};

Tui::render($app)->waitUntilExit();
```

---

## Dashboard with Multiple Widgets

A dashboard combining various widgets.

[[TODO:SCREENSHOT:dashboard example]]

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Xocdr\Tui\Tui;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Spacer;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Display\Tabs;
use Xocdr\Tui\Widgets\Display\TabItem;
use Xocdr\Tui\Widgets\Feedback\Badge;
use Xocdr\Tui\Widgets\Feedback\Meter;
use Xocdr\Tui\Widgets\Feedback\Alert;
use Xocdr\Tui\Widgets\Display\ItemList;

$app = function () {
    return Box::column([
        // Header
        Box::row([
            Text::create('🚀 System Dashboard')->bold(),
            Spacer::create(),
            Badge::success('Online'),
            Text::create(' '),
            Badge::info('v1.0.0'),
        ]),

        Text::create(''),

        // Stats row
        Box::row([
            Box::create()
                ->border('round')
                ->borderTitle('CPU')
                ->padding(1)
                ->children([
                    Meter::create()
                        ->value(65)
                        ->width(20)
                        ->colorByValue(true),
                ]),

            Text::create('  '),

            Box::create()
                ->border('round')
                ->borderTitle('Memory')
                ->padding(1)
                ->children([
                    Meter::create()
                        ->value(42)
                        ->width(20)
                        ->colorByValue(true),
                ]),

            Text::create('  '),

            Box::create()
                ->border('round')
                ->borderTitle('Disk')
                ->padding(1)
                ->children([
                    Meter::create()
                        ->value(78)
                        ->width(20)
                        ->colorByValue(true),
                ]),
        ]),

        Text::create(''),

        // Tabs
        Tabs::create([
            new TabItem('Processes', ItemList::create([
                'nginx (pid 1234)',
                'php-fpm (pid 5678)',
                'mysql (pid 9012)',
            ])),
            new TabItem('Logs', Text::create('Recent log entries...')),
            new TabItem('Alerts', Alert::warning('High memory usage detected')),
        ]),
    ]);
};

Tui::render($app)->waitUntilExit();
```

---

## Command Output Viewer

Display and monitor command output.

[[TODO:SCREENSHOT:command-viewer example]]

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Xocdr\Tui\Tui;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Hooks;
use Xocdr\Tui\Widgets\Content\OutputBlock;
use Xocdr\Tui\Widgets\Feedback\Badge;

$app = function () {
    $hooks = Hooks::current();

    [$output, $setOutput] = $hooks->state('');
    [$isRunning, $setIsRunning] = $hooks->state(true);

    // Simulate streaming output
    $hooks->interval(function () use ($output, $setOutput, $isRunning, $setIsRunning) {
        if (!$isRunning) return;

        static $line = 0;
        $line++;

        if ($line > 20) {
            $setIsRunning(false);
            return;
        }

        $setOutput($output . "Processing item {$line}...\n");
    }, 200);

    return Box::column([
        Box::row([
            Text::create('Build Output')->bold(),
            Text::create(' '),
            $isRunning
                ? Badge::loading('Running')
                : Badge::success('Complete'),
        ]),

        Text::create(''),

        OutputBlock::create($output)
            ->command('make build')
            ->streaming($isRunning)
            ->scrollable(true)
            ->maxLines(15)
            ->border('single'),
    ]);
};

Tui::render($app)->waitUntilExit();
```

---

## Multi-Select Options

Interactive multi-selection with search.

[[TODO:SCREENSHOT:multiselect example]]

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Xocdr\Tui\Tui;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Hooks;
use Xocdr\Tui\Widgets\Input\MultiSelect;
use Xocdr\Tui\Widgets\Feedback\Badge;

$app = function () {
    $hooks = Hooks::current();

    [$selected, $setSelected] = $hooks->state(['node', 'typescript']);

    $options = [
        'node' => 'Node.js',
        'python' => 'Python',
        'go' => 'Go',
        'rust' => 'Rust',
        'php' => 'PHP',
        'ruby' => 'Ruby',
        'java' => 'Java',
        'csharp' => 'C#',
        'typescript' => 'TypeScript',
        'swift' => 'Swift',
    ];

    return Box::column([
        Text::create('Select your favorite languages:')->bold(),
        Text::create(''),

        MultiSelect::create($options)
            ->selected($selected)
            ->min(1)
            ->max(5)
            ->enableSelectAll(true)
            ->onSubmit(fn($values) => $setSelected($values)),

        Text::create(''),

        Box::row([
            Text::create('Selected: '),
            ...array_map(
                fn($key) => Box::row([
                    Badge::info($options[$key]),
                    Text::create(' '),
                ]),
                $selected
            ),
        ]),

        Text::create(''),
        Text::create('Space to toggle, Enter to confirm, a/d for all')->dim(),
    ]);
};

Tui::render($app)->waitUntilExit();
```

---

## Loading States

Show loading progress and transitions.

[[TODO:SCREENSHOT:loading-states example]]

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Xocdr\Tui\Tui;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Hooks;
use Xocdr\Tui\Widgets\Feedback\LoadingState;
use Xocdr\Tui\Widgets\Feedback\Meter;

$app = function () {
    $hooks = Hooks::current();

    [$progress, $setProgress] = $hooks->state(0);
    [$state, $setState] = $hooks->state('loading');

    $hooks->interval(function () use ($progress, $setProgress, $setState) {
        if ($progress >= 100) {
            $setState('success');
            return;
        }
        $setProgress($progress + 5);
    }, 100);

    return Box::column([
        Text::create('Downloading update...')->bold(),
        Text::create(''),

        LoadingState::create()
            ->state($state)
            ->message('Downloading packages...')
            ->successMessage('Download complete!')
            ->successContent(
                Text::create('All packages have been downloaded successfully.')
            ),

        Text::create(''),

        $state === 'loading'
            ? Meter::create()
                ->value($progress)
                ->max(100)
                ->width(40)
                ->showValue(true)
                ->brackets(true)
            : null,
    ]);
};

Tui::render($app)->waitUntilExit();
```
