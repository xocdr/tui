# Display Widgets

Widgets for presenting data in structured formats.

## ItemList

Ordered and unordered lists with nesting support.

[[TODO:SCREENSHOT:itemlist with nested items and selection]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Display\ItemList;

ItemList::create([
    'First item',
    'Second item',
    'Third item',
]);
```

### Ordered Lists

```php
ItemList::ordered([
    'Step one',
    'Step two',
    'Step three',
])->startNumber(1);
```

### Nested Items

```php
use Xocdr\Tui\Widgets\Display\ListItem;

ItemList::create([
    new ListItem('Parent item', [
        'Child item 1',
        'Child item 2',
        new ListItem('Nested parent', [
            'Deeply nested',
        ]),
    ]),
    'Another top-level item',
]);
```

### Interactive Lists

```php
ItemList::create($items)
    ->interactive(true)
    ->onSelect(fn($item, $index) => handleSelect($item));
```

### Bullet Styles

```php
ItemList::create($items)->bulletStyle('disc');   // • (default)
ItemList::create($items)->bulletStyle('circle'); // ○
ItemList::create($items)->bulletStyle('square'); // ■
ItemList::create($items)->bulletStyle('dash');   // -
ItemList::create($items)->bulletStyle('arrow');  // →
```

### Scrolling for Long Lists

```php
ItemList::create($manyItems)
    ->maxVisible(10)
    ->smoothScroll(true);
```

### Configuration Options

| Method | Description |
|--------|-------------|
| `title($str)` | List title |
| `variant('ordered'\|'unordered')` | List type |
| `bulletStyle($style)` | Bullet character style |
| `showNumbers($bool)` | Show item numbers |
| `startNumber($int)` | Starting number for ordered lists |
| `indent($spaces)` | Left indentation |
| `nestedIndent($spaces)` | Indentation per nesting level |
| `maxVisible($int)` | Maximum visible items |
| `smoothScroll($bool)` | Enable smooth scrolling |
| `interactive($bool)` | Enable keyboard navigation |
| `onSelect($fn)` | Selection callback |

---

## Tree

Expandable tree view for hierarchical data.

[[TODO:SCREENSHOT:tree with expanded and collapsed nodes]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Display\Tree;
use Xocdr\Tui\Widgets\Display\TreeNode;

Tree::create([
    new TreeNode('src', [
        new TreeNode('Components'),
        new TreeNode('Widgets', [
            new TreeNode('Input'),
            new TreeNode('Display'),
        ]),
    ]),
    new TreeNode('tests'),
    new TreeNode('docs'),
]);
```

### Interactive Tree

```php
Tree::create($nodes)
    ->interactive(true)
    ->onSelect(fn($node) => openFile($node))
    ->onToggle(fn($node, $expanded) => handleToggle($node));
```

### Multi-Select Mode

```php
Tree::create($nodes)
    ->interactive(true)
    ->multiSelect(true)
    ->onMultiSelect(fn($nodes) => handleMultiSelect($nodes));
```

### Searchable Tree

```php
Tree::create($nodes)
    ->interactive(true)
    ->searchable(true)
    ->filterPlaceholder('Type to filter...')
    ->emptyFilterText('No matching nodes');
```

### Custom Icons

```php
Tree::create($nodes)
    ->expandedIcon('▼')
    ->collapsedIcon('▶')
    ->leafIcon('•')
    ->folderIcon('📁')
    ->fileIcon('📄');
```

### Configuration Options

| Method | Description |
|--------|-------------|
| `label($str)` | Tree label/title |
| `interactive($bool)` | Enable keyboard navigation |
| `showIcons($bool)` | Show folder/file icons |
| `showGuides($bool)` | Show tree guide lines |
| `guideStyle('ascii'\|'unicode')` | Guide line style |
| `expandAll($bool)` | Start with all nodes expanded |
| `collapseAll($bool)` | Start with all nodes collapsed |
| `indentSize($int)` | Indentation per level |
| `multiSelect($bool)` | Enable multi-selection |
| `searchable($bool)` | Enable filtering |
| `pageSize($int)` | Visible items for pagination |
| `smoothScroll($bool)` | Enable smooth scrolling |

### Keyboard Navigation

| Key | Action |
|-----|--------|
| `↑` / `k` | Move up |
| `↓` / `j` | Move down |
| `←` / `h` | Collapse node |
| `→` / `l` | Expand node |
| `Enter` | Select / Toggle |
| `Space` / `Tab` | Toggle selection (multi-select) |
| `*` | Expand all |
| `-` | Collapse all |
| `g` | Go to first |
| `G` | Go to last |

---

## TodoList

Task list with status indicators and animations.

[[TODO:SCREENSHOT:todolist with different status states]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Display\TodoList;
use Xocdr\Tui\Widgets\Display\TodoItem;
use Xocdr\Tui\Widgets\Display\TodoStatus;

TodoList::create([
    new TodoItem('Buy groceries', TodoStatus::COMPLETED),
    new TodoItem('Write documentation', TodoStatus::IN_PROGRESS),
    new TodoItem('Review pull request', TodoStatus::PENDING),
]);
```

### Interactive Mode

```php
TodoList::create($todos)
    ->interactive(true)
    ->onStatusChange(fn($id, $status) => updateStatus($id, $status));
```

### With Durations

```php
TodoList::create([
    new TodoItem('Task 1', TodoStatus::COMPLETED, duration: '2m 30s'),
    new TodoItem('Task 2', TodoStatus::IN_PROGRESS, duration: '1m 15s'),
])
->showDurations(true);
```

### Show Progress

```php
TodoList::create($todos)
    ->showProgress(true)
    ->progressFormat('{done}/{total} complete');
```

### Nested Tasks

```php
TodoList::create([
    new TodoItem('Main task', TodoStatus::IN_PROGRESS, subtasks: [
        new TodoItem('Subtask 1', TodoStatus::COMPLETED),
        new TodoItem('Subtask 2', TodoStatus::PENDING),
    ]),
])
->nestable(true);
```

### Custom Status Colors

```php
TodoList::create($todos)
    ->statusColors([
        'pending' => 'gray',
        'in_progress' => 'cyan',
        'completed' => 'green',
        'blocked' => 'red',
    ])
    ->colorIcons(true)
    ->colorText(false);
```

### Status Values

| Status | Icon | Color |
|--------|------|-------|
| `pending` | ○ | gray |
| `in_progress` | ● (animated) | cyan |
| `completed` | ✓ | green |
| `blocked` | ✗ | red |
| `failed` | ✗ | red |
| `skipped` | ○ | gray |

---

## Checklist

Checkable item list with progress tracking.

[[TODO:SCREENSHOT:checklist with checked and unchecked items]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Display\Checklist;

Checklist::create([
    'Enable feature A',
    'Configure settings',
    'Run tests',
])->interactive(true);
```

### Pre-checked Items

```php
use Xocdr\Tui\Widgets\Display\ChecklistItem;

Checklist::create([
    new ChecklistItem('Already done', checked: true),
    new ChecklistItem('Not yet done', checked: false),
    new ChecklistItem('Disabled item', disabled: true),
]);
```

### With Progress

```php
Checklist::create($items)
    ->showProgress(true)
    ->progressFormat('{checked}/{total} ({percent}%)');
```

### Custom Icons

```php
Checklist::create($items)
    ->checkedIcon('✓')
    ->uncheckedIcon('○')
    ->checkedColor('green')
    ->uncheckedColor('gray')
    ->strikethroughChecked(true);
```

### Callbacks

```php
Checklist::create($items)
    ->onChange(fn($index, $checked) => handleChange($index, $checked))
    ->onComplete(fn() => allItemsChecked());
```

---

## Tabs

Tab navigation with content switching.

[[TODO:SCREENSHOT:tabs with active tab highlighted]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Display\Tabs;
use Xocdr\Tui\Widgets\Display\TabItem;

Tabs::create([
    new TabItem('Home', $homeContent),
    new TabItem('Settings', $settingsContent),
    new TabItem('Help', $helpContent),
]);
```

### With Icons and Badges

```php
Tabs::create([
    new TabItem('Files', $content, icon: '📁'),
    new TabItem('Search', $content, icon: '🔍', badge: '3'),
    new TabItem('Settings', $content, icon: '⚙️'),
])
->showIcons(true)
->showBadges(true);
```

### Tab Variants

```php
// Default style with separator
Tabs::create($tabs)->variant('default')->separator(' | ');

// Boxed style with borders
Tabs::create($tabs)->variant('boxed');
```

### Closable Tabs

```php
Tabs::create($tabs)
    ->closable(true)
    ->onClose(fn($index, $tab) => closeTab($index));
```

### Configuration Options

| Method | Description |
|--------|-------------|
| `activeIndex($int)` | Initial active tab |
| `variant('default'\|'boxed')` | Tab style |
| `separator($str)` | Separator between tabs |
| `activeColor($color)` | Active tab color |
| `inactiveColor($color)` | Inactive tab color |
| `showIcons($bool)` | Show tab icons |
| `showBadges($bool)` | Show tab badges |
| `wrap($bool)` | Wrap to first/last on navigation |
| `maxVisibleTabs($int)` | Limit visible tabs |
| `closable($bool)` | Enable tab closing |
| `onChange($fn)` | Tab change callback |

### Keyboard Navigation

- `←` / `h` - Previous tab
- `→` / `l` - Next tab
- `1-9` - Jump to tab by number
- `x` - Close tab (if closable)

---

## Breadcrumb

Navigation path display.

[[TODO:SCREENSHOT:breadcrumb with path segments]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Display\Breadcrumb;

Breadcrumb::create(['Home', 'Products', 'Electronics', 'Phones']);
```

### With Icons

```php
Breadcrumb::create([
    ['label' => 'Home', 'icon' => '🏠'],
    ['label' => 'Documents', 'icon' => '📁'],
    ['label' => 'report.pdf', 'icon' => '📄'],
]);
```

### Custom Separator

```php
Breadcrumb::create($segments)->separator(' > ');
Breadcrumb::create($segments)->separator(' → ');
```

### With Truncation

```php
Breadcrumb::create($longPath)
    ->maxWidth(40)
    ->truncate('middle');  // 'start', 'middle', 'end'
```

### Interactive Mode

```php
Breadcrumb::create($segments)
    ->interactive(true)
    ->onSelect(fn($index, $segment) => navigateTo($segment));
```

### Styling

```php
Breadcrumb::create($segments)
    ->activeColor('cyan')
    ->inactiveColor('gray')
    ->currentStyle(['bold' => true, 'color' => 'cyan']);
```

---

## StatusBar

Status information bar for displaying context.

[[TODO:SCREENSHOT:statusbar with left, center, and right sections]]

### Basic Usage

```php
use Xocdr\Tui\Widgets\Display\StatusBar;

StatusBar::create()
    ->left('Ready')
    ->right('Ctrl+C to exit');
```

### With Center Content

```php
StatusBar::create()
    ->left('src/main.php')
    ->center('Line 42, Col 15')
    ->right('UTF-8 | PHP');
```

### With Colors

```php
StatusBar::create()
    ->left('● Connected', 'green')
    ->right('2 errors', 'red');
```

Note: Full StatusBar implementation may vary. Check the actual widget for all available methods.

---

## See Also

- [TodoList Reference](../../reference/widgets/todolist.md) - TodoList API
- [Tree Reference](../../reference/widgets/tree.md) - Tree widget API
- [Tabs Reference](../../reference/widgets/tabs.md) - Tabs widget API
- [Widget Manual](index.md) - Widget overview
