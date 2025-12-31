# TodoList

A widget for displaying task lists with status indicators, supporting readonly and interactive modes.

## Namespace

```php
use Xocdr\Tui\Widgets\Display\TodoList;
use Xocdr\Tui\Widgets\Display\TodoItem;
use Xocdr\Tui\Widgets\Display\TodoStatus;
```

## Overview

The TodoList widget displays a list of tasks with status indicators, colors, and optional animations. It supports:

- Multiple status types (pending, in_progress, completed, blocked, failed, skipped)
- Status-based icons and colors
- Animated spinner for in-progress items
- Strikethrough for completed items
- Duration tracking and display
- Progress display (e.g., "3/5 complete")
- Subtasks with nesting
- Active task title bar
- Toggle visibility (collapsible)
- Readonly mode (agent-managed) or interactive mode (user-managed)

## Console Appearance

**Readonly mode with active task:**
```
⠋ Running tests... (esc to interrupt · ctrl+t to show todos)
└ ✓ Analyze requirements
  ⠋ Running unit tests
  ○ Deploy to staging
  ○ Update documentation
```

**Collapsed/hidden mode:**
```
●  Next: Running unit tests
```

**Interactive mode with selection:**
```
  ✓ Task completed
› ● Currently working on this
  ○ Pending task
  ✗ Blocked task
```

## Basic Usage

```php
// Simple readonly list
TodoList::create([
    ['content' => 'First task', 'status' => 'completed'],
    ['content' => 'Second task', 'status' => 'in_progress'],
    ['content' => 'Third task', 'status' => 'pending'],
])->readonly();

// Using TodoItem objects
TodoList::create([
    new TodoItem('1', 'First task', 'Working on first...', TodoStatus::COMPLETED),
    new TodoItem('2', 'Second task', 'Working on second...', TodoStatus::IN_PROGRESS),
]);

// Interactive mode
TodoList::create($todos)
    ->interactive()
    ->onStatusChange(fn($id, $status) => saveStatus($id, $status));
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `TodoList::create(array $todos = [])` | Create a new TodoList |

## Configuration Methods

### Display Options

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `todos(array $todos)` | array | [] | Set todo items |
| `readonly(bool)` | bool | true | Disable user interaction |
| `interactive(bool)` | bool | false | Enable keyboard navigation |
| `maxItems(int $max)` | int | null | Maximum visible items |
| `showSpinner(bool)` | bool | true | Animate in_progress items |
| `showActiveTaskTitle(bool)` | bool | false | Show active task in header |

### Title Bar Options

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `canInterrupt(bool)` | bool | true | Show interrupt hint |
| `canHideTodos(bool)` | bool | true | Allow hiding todos |
| `keyToInterrupt(string)` | string | 'esc' | Key to interrupt |
| `keyToHideTodos(string)` | string | 'ctrl+t' | Key to toggle visibility |
| `titleCallback(callable)` | callable | null | Dynamic title content |
| `titleColor(string)` | string | null | Title text color |
| `titleAdditionalColor(string)` | string | 'dim' | Additional info color |

### Styling Options

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `statusColors(array)` | array | [...] | Custom colors per status |
| `colorIcons(bool)` | bool | true | Apply status color to icons |
| `colorText(bool)` | bool | false | Apply status color to text |

### Duration & Progress

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `showDurations(bool)` | bool | false | Show task durations |
| `durationColor(string)` | string | 'dim' | Duration text color |
| `showProgress(bool)` | bool | false | Show progress summary |
| `progressFormat(string)` | string | '{done}/{total} complete' | Progress format |

### Subtasks

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `nestable(bool)` | bool | false | Enable subtasks |
| `indentSize(int)` | int | 2 | Indent chars for subtasks |

### Callbacks

| Method | Type | Description |
|--------|------|-------------|
| `onStatusChange(callable)` | callable | Called when status changes |
| `onAdd(callable)` | callable | Called when item added |
| `onDelete(callable)` | callable | Called when item deleted |

## TodoItem Class

```php
class TodoItem
{
    public string $id;
    public string $content;
    public string $activeForm;
    public TodoStatus $status;
    public ?string $duration = null;
    public array $subtasks = [];

    public function __construct(
        string $id,
        string $content,
        string $activeForm = '',
        TodoStatus $status = TodoStatus::PENDING,
        ?string $duration = null,
        array $subtasks = [],
    );

    public static function from(array $data): self;
}
```

### Creating TodoItems

```php
// From array
$item = TodoItem::from([
    'id' => '1',
    'content' => 'Task description',
    'activeForm' => 'Working on task...',
    'status' => 'in_progress',
    'duration' => '1.5s',
]);

// Using constructor
$item = new TodoItem(
    id: '1',
    content: 'Task description',
    activeForm: 'Working on task...',
    status: TodoStatus::IN_PROGRESS,
    duration: '1.5s',
);
```

## TodoStatus Enum

```php
enum TodoStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case BLOCKED = 'blocked';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';
}
```

### Default Status Configuration

| Status | Icon | Color | Extra |
|--------|------|-------|-------|
| `pending` | ○ | gray | |
| `in_progress` | ● | cyan | animated spinner |
| `completed` | ✓ | green | strikethrough |
| `blocked` | ✗ | red | |
| `failed` | ✗ | red | |
| `skipped` | ○ | gray | |

## Examples

### Readonly Agent-Managed List

```php
TodoList::create([
    ['content' => 'Analyze code', 'status' => 'completed'],
    ['content' => 'Run tests', 'status' => 'in_progress', 'activeForm' => 'Running tests...'],
    ['content' => 'Deploy', 'status' => 'pending'],
])
    ->readonly()
    ->showSpinner(true)
    ->showActiveTaskTitle(true);
```

### Interactive User-Managed List

```php
TodoList::create($todos)
    ->interactive()
    ->onStatusChange(function ($id, $status) {
        // Save to database
        updateTaskStatus($id, $status);
    });
```

### With Duration Tracking

```php
$todos = [
    new TodoItem('1', 'Database migration', '', TodoStatus::COMPLETED, '2.3s'),
    new TodoItem('2', 'Running tests', 'Testing...', TodoStatus::IN_PROGRESS, '45s'),
    new TodoItem('3', 'Deploy', '', TodoStatus::PENDING),
];

TodoList::create($todos)
    ->showDurations(true)
    ->durationColor('cyan');
```

### With Subtasks

```php
$todos = [
    new TodoItem(
        id: '1',
        content: 'Setup project',
        status: TodoStatus::IN_PROGRESS,
        subtasks: [
            new TodoItem('1.1', 'Install dependencies', '', TodoStatus::COMPLETED),
            new TodoItem('1.2', 'Configure database', '', TodoStatus::IN_PROGRESS),
            new TodoItem('1.3', 'Setup environment', '', TodoStatus::PENDING),
        ],
    ),
];

TodoList::create($todos)
    ->nestable(true)
    ->indentSize(2);
```

### With Progress Display

```php
TodoList::create($todos)
    ->showProgress(true)
    ->progressFormat('{done} of {total} tasks complete');
```

### With Dynamic Title Content

```php
$startTime = time();

TodoList::create($todos)
    ->showActiveTaskTitle(true)
    ->titleCallback(function () use ($startTime) {
        $elapsed = time() - $startTime;
        return gmdate('i:s', $elapsed);
    });
```

### Custom Status Colors

```php
TodoList::create($todos)
    ->statusColors([
        'completed' => ['icon' => '✔', 'color' => 'blue'],
        'in_progress' => ['icon' => '→', 'color' => 'yellow'],
    ]);
```

## Keyboard Navigation (Interactive Mode)

| Key | Action |
|-----|--------|
| `↑` / `k` | Move selection up |
| `↓` / `j` | Move selection down |
| `Space` | Cycle status (pending → in_progress → completed) |

## See Also

- [Checklist](./checklist.md) - Simpler checkbox list
- [Icon](./icon.md) - Status icons used in TodoList
- [LoadingState](./loadingstate.md) - Loading indicators
