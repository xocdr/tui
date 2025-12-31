# Tabs

A tabbed interface widget for switching between multiple views.

## Namespace

```php
use Xocdr\Tui\Widgets\Display\Tabs;
use Xocdr\Tui\Widgets\Display\TabItem;
```

## Overview

The Tabs widget provides a horizontal tab bar for switching between views. Features include:

- Horizontal tab bar with multiple tabs
- Active tab highlighting
- Tab labels with optional icons and badges
- Keyboard navigation
- Scrolling for many tabs
- Optional close buttons
- Multiple visual styles

## Console Appearance

**Default style:**
```
Tab 1 | Tab 2 | Tab 3
^active (bold, colored)
```

**Boxed style:**
```
╭─────────╮ ╭───────────╮ ╭───────────╮
│ ◉ Main  │ │ ○ Feature │ │ ○ Bugfix  │
╰─────────╯ ╰───────────╯ ╰───────────╯
  ^active     ^inactive     ^inactive
```

## Basic Usage

```php
// Simple tabs
Tabs::create([
    ['label' => 'Main'],
    ['label' => 'Settings'],
    ['label' => 'Help'],
])
->activeIndex(0)
->onChange(fn($index, $tab) => switchView($index));

// With icons and badges
Tabs::create()
    ->addTab('Main', $mainContent, '📁')
    ->addTab('Running', $runningContent, '🔄')
    ->addTab('Errors', $errorContent, '❌');
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Tabs::create(array $tabs = [])` | Create with optional initial tabs |

## Configuration Methods

### Tab Data

| Method | Description |
|--------|-------------|
| `tabs(array)` | Set all tabs |
| `addTab(label, content?, icon?)` | Add a single tab |
| `activeIndex(int)` | Set active tab index |

### Display

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `variant(string)` | string | 'default' | 'default' or 'boxed' |
| `separator(string)` | string | ' \| ' | Tab separator |
| `activeColor(string)` | string | 'cyan' | Active tab color |
| `inactiveColor(string)` | string | 'white' | Inactive tab color |
| `showIcons(bool)` | bool | true | Show tab icons |
| `showBadges(bool)` | bool | true | Show tab badges |

### Behavior

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `interactive(bool)` | bool | true | Enable keyboard navigation |
| `wrap(bool)` | bool | false | Wrap at tab boundaries |
| `maxVisibleTabs(int?)` | int | null | Max visible tabs (scrolling) |
| `closable(bool)` | bool | false | Show close buttons |

### Callbacks

| Method | Description |
|--------|-------------|
| `onChange(callable)` | Called when tab changes |
| `onClose(callable)` | Called when tab closed |

## TabItem Class

```php
class TabItem
{
    public string $label;
    public mixed $content = null;
    public ?string $icon = null;
    public ?string $badge = null;
}
```

## Keyboard Navigation

| Key | Action |
|-----|--------|
| `←` / `h` | Previous tab |
| `→` / `l` | Next tab |
| `1-9` | Select tab 1-9 |
| `x` | Close tab (if closable) |

## Examples

### Basic Tabs

```php
Tabs::create([
    ['label' => 'Overview'],
    ['label' => 'Details'],
    ['label' => 'Settings'],
])
->activeIndex(0)
->onChange(fn($index) => showPanel($index));
```

### Boxed Style with Icons

```php
Tabs::create()
    ->variant('boxed')
    ->addTab('Files', null, '📁')
    ->addTab('Search', null, '🔍')
    ->addTab('Extensions', null, '🧩')
    ->activeColor('green');
```

### Closable Tabs

```php
Tabs::create($openFiles)
    ->closable()
    ->onClose(fn($index, $tab) => closeFile($index))
    ->onChange(fn($index) => switchToFile($index));
```

### With Badge Counts

```php
Tabs::create([
    new TabItem('Inbox', null, '📥', badge: '12'),
    new TabItem('Sent', null, '📤', badge: '3'),
    new TabItem('Drafts', null, '📝', badge: '1'),
])
->showBadges();
```

### Scrollable Tabs

```php
Tabs::create($manyTabs)
    ->maxVisibleTabs(5)
    ->wrap();
```

## See Also

- [Breadcrumb](./breadcrumb.md) - Navigation breadcrumbs
- [SelectList](./selectlist.md) - List selection
