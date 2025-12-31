# Getting Started

This guide will walk you through creating your first TUI application with widgets.

## Your First Application

Let's create a simple interactive todo list application:

```php
<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Xocdr\Tui\Tui;
use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Display\TodoList;
use Xocdr\Tui\Widgets\Display\TodoItem;
use Xocdr\Tui\Widgets\Display\TodoStatus;
use Xocdr\Tui\Widgets\Input\Input;
use Xocdr\Tui\Widgets\Feedback\Alert;

if (!Tui::isInteractive()) {
    echo "Error: Requires interactive terminal.\n";
    exit(1);
}

$app = function () {
    return Box::column([
        Text::create('My Todo List')->bold(),
        Text::create(''),

        TodoList::create([
            new TodoItem('Buy groceries', TodoStatus::Completed),
            new TodoItem('Write documentation', TodoStatus::InProgress),
            new TodoItem('Review pull request', TodoStatus::Pending),
        ]),

        Text::create(''),
        Text::create('Press Ctrl+C to exit')->dim(),
    ]);
};

Tui::render($app)->waitUntilExit();
```

[[TODO:SCREENSHOT:todo-list example running in terminal]]

## Understanding Widget Structure

TUI widgets follow a consistent pattern:

### Creating Widgets

All widgets use a static `create()` factory method:

```php
// Create with defaults
$input = Input::create();

// Create with initial values
$list = TodoList::create($items);
```

### Fluent Configuration

Widgets support fluent method chaining:

```php
$input = Input::create()
    ->placeholder('Enter text...')
    ->value('Initial value')
    ->onSubmit(fn($value) => doSomething($value));
```

### Building Components

Widgets implement the `build()` method which returns a component tree:

```php
// Widgets are used directly in component trees
Box::column([
    $input,           // Widget instance
    $todoList,        // Widget instance
    Text::create(''), // Regular component
]);
```

## Working with State

Widgets manage their own internal state using hooks. You can also connect widgets to external state:

```php
$app = function () {
    $hooks = Hooks::current();

    [$todos, $setTodos] = $hooks->state([
        new TodoItem('Task 1', TodoStatus::Pending),
    ]);

    return Box::column([
        TodoList::create($todos)
            ->onStatusChange(function ($item, $status) use ($setTodos) {
                // Update external state
            }),

        Input::create()
            ->placeholder('Add new todo...')
            ->onSubmit(function ($text) use ($setTodos, $todos) {
                $setTodos([...$todos, new TodoItem($text)]);
            }),
    ]);
};
```

## Handling User Input

Widgets provide callback props for handling user interactions:

### Input Callbacks

```php
Input::create()
    ->onSubmit(fn($value) => ...)    // Enter pressed
    ->onChange(fn($value) => ...)     // Value changed
    ->onCancel(fn() => ...)           // Escape pressed
    ->onFocus(fn() => ...)            // Input focused
    ->onBlur(fn() => ...);            // Input blurred
```

### List Callbacks

```php
SelectList::create($options)
    ->onSelect(fn($value) => ...)     // Item selected
    ->onToggle(fn($value, $on) => ...); // Multi-select toggle
```

## Keyboard Navigation

Most widgets support keyboard navigation:

| Key | Action |
|-----|--------|
| `↑` / `k` | Move up |
| `↓` / `j` | Move down |
| `←` / `h` | Collapse / Move left |
| `→` / `l` | Expand / Move right |
| `Enter` | Select / Submit |
| `Space` | Toggle |
| `Escape` | Cancel |
| `Tab` | Next focus |
| `Shift+Tab` | Previous focus |

## Styling Widgets

Widgets accept styling through their fluent API:

### Colors

```php
Alert::create('Message')
    ->variant('success')  // Preset variant
    ->color('green');     // Custom color

// Colors support Tailwind palette syntax
Badge::create('Status')
    ->color('blue', 500);  // blue-500
```

### Icons

```php
TodoList::create($items)
    ->pendingIcon('○')
    ->completedIcon('✓')
    ->inProgressIcon('◐');
```

## Combining Widgets

Build complex interfaces by composing widgets:

```php
$app = function () {
    return Box::column([
        // Header
        Box::row([
            Text::create('My App')->bold(),
            Spacer::create(),
            Badge::create('v1.0')->color('blue'),
        ]),

        // Navigation
        Tabs::create([
            TabItem::create('Home'),
            TabItem::create('Settings'),
        ]),

        // Content
        Scrollable::create([
            // ... scrollable content
        ])->height(10),

        // Footer
        StatusBar::create()
            ->left('Ready')
            ->right('Ctrl+C to exit'),
    ]);
};
```

[[TODO:SCREENSHOT:complex-app showing composed widgets]]

## Next Steps

- [Input Widgets](input-widgets.md) - Text input, selection, forms
- [Display Widgets](display-widgets.md) - Lists, trees, tables
- [Feedback Widgets](feedback-widgets.md) - Alerts, badges, progress
- [Layout Widgets](layout-widgets.md) - Scrollable, dividers, collapsible
- [Content Widgets](content-widgets.md) - Markdown, diff, paragraphs
- [Advanced Topics](advanced.md) - Custom widgets, theming

## See Also

- [Widget Overview](../widgets.md) - Creating custom widgets from scratch
- [Widget API Reference](../../reference/widgets/index.md) - Complete API documentation
- [Hooks](../hooks.md) - State management for widgets
