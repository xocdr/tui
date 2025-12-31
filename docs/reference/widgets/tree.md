# Tree

A hierarchical tree view widget with expand/collapse support.

## Namespace

```php
use Xocdr\Tui\Widgets\Display\Tree;
use Xocdr\Tui\Widgets\Display\TreeNode;
```

## Overview

The Tree widget displays hierarchical data. Features include:

- Expand/collapse nodes
- Interactive navigation with vim-style keys
- Multi-select support
- Search/filter within tree
- File/folder icons
- Tree guides (ascii/unicode)
- Custom node rendering

## Console Appearance

```
📁 src
├─ 📁 Components
│  ├─ 📄 Box.php
│  └─ 📄 Text.php
└─ 📁 Widgets
   └─ 📄 Tree.php
```

**With search:**
```
/ index█
› 📄 index.php
  📄 index.html
```

**With multi-select:**
```
◉ › 📄 file1.php
○   📄 file2.php
◉   📄 file3.php
```

## Basic Usage

```php
Tree::create([
    new TreeNode('src', [
        new TreeNode('Components', [
            new TreeNode('Box.php'),
            new TreeNode('Text.php'),
        ]),
    ]),
])
->interactive()
->expandAll();
```

## Static Constructors

| Method | Description |
|--------|-------------|
| `Tree::create(array)` | Create tree |

## Configuration Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `nodes(array)` | array | [] | Tree nodes |
| `addNode(label, children, expanded)` | - | - | Add node |
| `label(string?)` | string | null | Tree label |
| `interactive(bool)` | bool | false | Enable navigation |
| `showIcons(bool)` | bool | true | Show icons |
| `showGuides(bool)` | bool | true | Show tree guides |
| `guideStyle(string)` | string | 'ascii' | 'ascii' or 'unicode' |
| `expandAll(bool)` | bool | false | Start expanded |
| `collapseAll(bool)` | bool | false | Start collapsed |
| `folderIcon(string)` | string | '📁' | Folder icon |
| `fileIcon(string)` | string | '📄' | File icon |
| `onSelect(callable)` | callable | null | Leaf select callback |
| `onToggle(callable)` | callable | null | Expand/collapse callback |

### Multi-Select Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `multiSelect(bool)` | bool | false | Enable multi-select |
| `onMultiSelect(callable)` | callable | null | Called with selected nodes array |

### Search/Filter Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `searchable(bool)` | bool | false | Enable search/filter |
| `filterFn(callable)` | callable | null | Custom filter function `fn(TreeNode, string): bool` |
| `filterPlaceholder(string?)` | string | 'Type to filter...' | Placeholder text |
| `emptyFilterText(string?)` | string | 'No matching nodes' | Empty results text |

### Navigation Methods

| Method | Type | Default | Description |
|--------|------|---------|-------------|
| `pageSize(int)` | int | 10 | Lines for page up/down |

## Keyboard Navigation

| Key | Action |
|-----|--------|
| `↑` / `k` | Move up |
| `↓` / `j` | Move down |
| `←` / `h` | Collapse node |
| `→` / `l` | Expand node |
| `Enter` / `Space` | Toggle/select |
| `g` | Go to first node |
| `G` | Go to last node |
| `u` | Page up |
| `d` | Page down |
| `*` | Expand all |
| `-` | Collapse all |
| `Tab` / `Space` | Toggle selection (multi-select mode) |
| `Esc` | Clear filter (search mode) |

## TreeNode Class

TreeNode is immutable. Use `with*()` methods to create modified copies:

```php
class TreeNode {
    public readonly string $label;
    public readonly array $children;
    public readonly bool $expanded;
    public readonly ?string $icon;
    public readonly ?string $badge;

    // Immutable modifiers
    public function withChild(TreeNode|array|string $child): self;
    public function withExpanded(bool $expanded): self;
}
```

## Examples

### Multi-Select Tree

```php
Tree::create($nodes)
    ->interactive()
    ->multiSelect()
    ->onMultiSelect(function (array $selectedNodes) {
        foreach ($selectedNodes as $node) {
            echo $node->label . "\n";
        }
    });
```

### Searchable Tree

```php
Tree::create($nodes)
    ->interactive()
    ->searchable()
    ->filterPlaceholder('Search files...')
    ->filterFn(fn ($node, $query) =>
        stripos($node->label, $query) !== false
    );
```

## See Also

- [Collapsible](./collapsible.md) - Single collapsible
- [ItemList](./itemlist.md) - Flat list
