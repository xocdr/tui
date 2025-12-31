<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Display;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Scroll\SmoothScroller;
use Xocdr\Tui\Scroll\VirtualList;
use Xocdr\Tui\Widgets\Widget;

class Tree extends Widget
{
    /** @var array<TreeNode> */
    private array $nodes = [];

    private ?string $label = null;

    private bool $interactive = false;

    private bool $showIcons = true;

    private bool $showGuides = true;

    private string $guideStyle = 'ascii';

    private string $expandedIcon = '▼';

    private string $collapsedIcon = '▶';

    private string $leafIcon = '•';

    private string $folderIcon = '📁';

    private string $fileIcon = '📄';

    private bool $expandAll = false;

    private bool $collapseAll = false;

    private int $indentSize = 2;

    private bool $multiSelect = false;

    private bool $searchable = false;

    private ?int $pageSize = null;

    private ?string $filterPlaceholder = 'Type to filter...';

    private ?string $emptyFilterText = 'No matching nodes';

    private bool $smoothScroll = true;

    private int $overscan = 3;

    /** @var callable|null */
    private $onSelect = null;

    /** @var callable|null */
    private $onToggle = null;

    /** @var callable|null */
    private $onMultiSelect = null;

    /** @var callable|null */
    private $renderNode = null;

    /** @var callable|null */
    private $filterFn = null;

    /**
     * @param array<TreeNode|array{label: string, children?: array<mixed>, expanded?: bool, icon?: string|null, value?: mixed, badge?: string|null}|string> $nodes
     */
    private function __construct(array $nodes = [])
    {
        $this->nodes($nodes);
    }

    /**
     * @param array<TreeNode|array{label: string, children?: array<mixed>, expanded?: bool, icon?: string|null, value?: mixed, badge?: string|null}|string> $nodes
     */
    public static function create(array $nodes = []): self
    {
        return new self($nodes);
    }

    /**
     * @param array<TreeNode|array{label: string, children?: array<mixed>, expanded?: bool, icon?: string|null, value?: mixed, badge?: string|null}|string> $nodes
     */
    public function nodes(array $nodes): self
    {
        $this->nodes = [];

        foreach ($nodes as $node) {
            if ($node instanceof TreeNode) {
                $this->nodes[] = $node;
            } else {
                $this->nodes[] = TreeNode::from($node);
            }
        }

        return $this;
    }

    /**
     * @param array<TreeNode|array{label: string, children?: array<mixed>, expanded?: bool, icon?: string|null, value?: mixed, badge?: string|null}|string> $children
     */
    public function addNode(string $label, array $children = [], bool $expanded = false): self
    {
        $this->nodes[] = new TreeNode($label, $children, $expanded);

        return $this;
    }

    public function label(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function interactive(bool $interactive = true): self
    {
        $this->interactive = $interactive;

        return $this;
    }

    public function showIcons(bool $show = true): self
    {
        $this->showIcons = $show;

        return $this;
    }

    public function showGuides(bool $show = true): self
    {
        $this->showGuides = $show;

        return $this;
    }

    public function guideStyle(string $style): self
    {
        $this->guideStyle = $style;

        return $this;
    }

    public function expandedIcon(string $icon): self
    {
        $this->expandedIcon = $icon;

        return $this;
    }

    public function collapsedIcon(string $icon): self
    {
        $this->collapsedIcon = $icon;

        return $this;
    }

    public function leafIcon(string $icon): self
    {
        $this->leafIcon = $icon;

        return $this;
    }

    public function folderIcon(string $icon): self
    {
        $this->folderIcon = $icon;

        return $this;
    }

    public function fileIcon(string $icon): self
    {
        $this->fileIcon = $icon;

        return $this;
    }

    public function expandAll(bool $expand = true): self
    {
        $this->expandAll = $expand;

        return $this;
    }

    public function collapseAll(bool $collapse = true): self
    {
        $this->collapseAll = $collapse;

        return $this;
    }

    public function indentSize(int $size): self
    {
        $this->indentSize = $size;

        return $this;
    }

    public function onSelect(callable $callback): self
    {
        $this->onSelect = $callback;

        return $this;
    }

    public function onToggle(callable $callback): self
    {
        $this->onToggle = $callback;

        return $this;
    }

    public function renderNode(callable $callback): self
    {
        $this->renderNode = $callback;

        return $this;
    }

    public function multiSelect(bool $enabled = true): self
    {
        $this->multiSelect = $enabled;

        return $this;
    }

    public function onMultiSelect(callable $callback): self
    {
        $this->onMultiSelect = $callback;

        return $this;
    }

    public function searchable(bool $enabled = true): self
    {
        $this->searchable = $enabled;

        return $this;
    }

    public function filterFn(callable $callback): self
    {
        $this->filterFn = $callback;

        return $this;
    }

    public function pageSize(?int $size): self
    {
        $this->pageSize = $size !== null ? max(1, $size) : null;

        return $this;
    }

    public function filterPlaceholder(?string $placeholder): self
    {
        $this->filterPlaceholder = $placeholder;

        return $this;
    }

    public function emptyFilterText(?string $text): self
    {
        $this->emptyFilterText = $text;

        return $this;
    }

    public function smoothScroll(bool $smooth = true): self
    {
        $this->smoothScroll = $smooth;

        return $this;
    }

    public function overscan(int $overscan): self
    {
        $this->overscan = max(0, $overscan);

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        if (empty($this->nodes)) {
            return Text::create('No nodes')->dim();
        }

        $flatNodes = $this->flattenNodes($this->nodes, $this->expandAll);

        /** @var int $selectedIndex */
        /** @var callable(int): void $setSelectedIndex */
        [$selectedIndex, $setSelectedIndex] = $hooks->state(0);
        /** @var array<string, bool> $expandedState */
        /** @var callable(array<string, bool>): void $setExpandedState */
        [$expandedState, $setExpandedState] = $hooks->state($this->getInitialExpandedState());
        /** @var array<string> $selectedPaths */
        /** @var callable(array<string>): void $setSelectedPaths */
        [$selectedPaths, $setSelectedPaths] = $hooks->state([]);
        /** @var string $filterText */
        /** @var callable(string): void $setFilterText */
        [$filterText, $setFilterText] = $hooks->state('');

        // Get visible nodes for current state
        $visibleNodes = $this->getVisibleNodes($flatNodes, $expandedState, $filterText);
        $nodeCount = count($visibleNodes);

        // Use VirtualList for efficient rendering of large trees
        // If pageSize is null, show all nodes (no scrolling)
        $viewportHeight = $this->pageSize ?? $nodeCount;
        $vlist = VirtualList::create(
            itemCount: $nodeCount,
            viewportHeight: $viewportHeight,
            itemHeight: 1,
            overscan: $this->overscan
        );

        // Use SmoothScroller for smooth scroll animations
        $scroller = $this->smoothScroll ? SmoothScroller::fast() : null;

        // Sync selected index with virtual list
        $vlist->scrollTo($selectedIndex);
        $range = $vlist->getVisibleRange();

        // Animate scroll position if smooth scrolling is enabled
        if ($scroller !== null) {
            $hooks->interval(function () use ($scroller) {
                if ($scroller->isAnimating()) {
                    $scroller->update(1.0 / 60.0);
                }
            }, 16);
        }

        if ($this->interactive) {
            $hooks->onInput(function ($key, $nativeKey) use (
                $setSelectedIndex,
                $expandedState,
                $setExpandedState,
                $setSelectedPaths,
                $filterText,
                $setFilterText,
                $flatNodes,
                $vlist,
                $scroller,
            ): void {
                $visibleNodes = $this->getVisibleNodes($flatNodes, $expandedState, $filterText);
                $nodeCount = count($visibleNodes);

                // Handle filter input when searchable
                if ($this->searchable && !$nativeKey->upArrow && !$nativeKey->downArrow
                    && !$nativeKey->leftArrow && !$nativeKey->rightArrow
                    && !$nativeKey->return && !$nativeKey->escape
                    // @phpstan-ignore notIdentical.alwaysTrue ($key can be null at runtime from input handler)
                    && $key !== null && strlen($key) === 1 && ctype_print($key)) {
                    $setFilterText(fn ($ft) => $ft . $key);
                    $setSelectedIndex(0);

                    return;
                }

                // Backspace in filter mode
                if ($this->searchable && $key === "\x7f" && strlen($filterText) > 0) {
                    $setFilterText(fn ($ft) => substr($ft, 0, -1));
                    $setSelectedIndex(0);

                    return;
                }

                // Escape clears filter
                if ($this->searchable && $nativeKey->escape && strlen($filterText) > 0) {
                    $setFilterText('');
                    $setSelectedIndex(0);

                    return;
                }

                // Navigation: Up
                if ($nativeKey->upArrow || $key === 'k') {
                    $setSelectedIndex(function ($idx) use ($vlist, $scroller) {
                        $newIndex = max(0, $idx - 1);
                        $vlist->ensureVisible($newIndex);

                        if ($scroller !== null) {
                            $scroller->setTarget(0.0, (float) $vlist->getItemOffset($newIndex));
                        }

                        return $newIndex;
                    });
                }

                // Navigation: Down
                if ($nativeKey->downArrow || $key === 'j') {
                    $setSelectedIndex(function ($idx) use ($nodeCount, $vlist, $scroller) {
                        $newIndex = min($nodeCount - 1, $idx + 1);
                        $vlist->ensureVisible($newIndex);

                        if ($scroller !== null) {
                            $scroller->setTarget(0.0, (float) $vlist->getItemOffset($newIndex));
                        }

                        return $newIndex;
                    });
                }

                // Navigation: Home (go to first)
                if ($key === 'g' || $key === "\x1b[H") {
                    $setSelectedIndex(0);
                    $vlist->scrollTo(0);

                    if ($scroller !== null) {
                        $scroller->setTarget(0.0, 0.0);
                    }
                }

                // Navigation: End (go to last)
                if ($key === 'G' || $key === "\x1b[F") {
                    $newIndex = max(0, $nodeCount - 1);
                    $setSelectedIndex($newIndex);
                    $vlist->ensureVisible($newIndex);

                    if ($scroller !== null) {
                        $scroller->setTarget(0.0, (float) $vlist->getItemOffset($newIndex));
                    }
                }

                // Navigation: Page up
                if ($key === "\x1b[5~" || $key === 'u') {
                    $setSelectedIndex(function ($idx) use ($vlist, $scroller) {
                        $newIndex = max(0, $idx - $this->pageSize);
                        $vlist->ensureVisible($newIndex);

                        if ($scroller !== null) {
                            $scroller->setTarget(0.0, (float) $vlist->getItemOffset($newIndex));
                        }

                        return $newIndex;
                    });
                }

                // Navigation: Page down
                if ($key === "\x1b[6~" || $key === 'd') {
                    $setSelectedIndex(function ($idx) use ($nodeCount, $vlist, $scroller) {
                        $newIndex = min($nodeCount - 1, $idx + $this->pageSize);
                        $vlist->ensureVisible($newIndex);

                        if ($scroller !== null) {
                            $scroller->setTarget(0.0, (float) $vlist->getItemOffset($newIndex));
                        }

                        return $newIndex;
                    });
                }

                // Multi-select toggle (Tab or Space when multiSelect enabled)
                if ($this->multiSelect && ($key === "\t" || $key === ' ')) {
                    $setSelectedIndex(function ($idx) use ($visibleNodes, $setSelectedPaths) {
                        $node = $visibleNodes[$idx] ?? null;
                        if ($node !== null) {
                            $setSelectedPaths(function ($currentPaths) use ($node, $visibleNodes) {
                                $newPaths = $currentPaths;
                                $path = $node['path'];
                                if (in_array($path, $newPaths, true)) {
                                    $newPaths = array_values(array_diff($newPaths, [$path]));
                                } else {
                                    $newPaths[] = $path;
                                }

                                if ($this->onMultiSelect !== null) {
                                    $selectedNodes = array_filter(
                                        $visibleNodes,
                                        fn ($n) => in_array($n['path'], $newPaths, true)
                                    );
                                    ($this->onMultiSelect)(array_map(fn ($n) => $n['node'], $selectedNodes));
                                }

                                return $newPaths;
                            });
                        }

                        return $idx;
                    });

                    return;
                }

                // Select/Toggle with Enter
                if ($nativeKey->return) {
                    $setSelectedIndex(function ($idx) use ($visibleNodes, $expandedState, $setExpandedState) {
                        $node = $visibleNodes[$idx] ?? null;
                        if ($node !== null) {
                            if (!empty($node['node']->children)) {
                                $setExpandedState(function ($currentState) use ($node) {
                                    $newState = $currentState;
                                    $newState[$node['path']] = !($currentState[$node['path']] ?? $node['node']->expanded);

                                    if ($this->onToggle !== null) {
                                        ($this->onToggle)($node['node'], $newState[$node['path']]);
                                    }

                                    return $newState;
                                });
                            } elseif ($this->onSelect !== null) {
                                ($this->onSelect)($node['node']);
                            }
                        }

                        return $idx;
                    });
                }

                // Expand with Right arrow
                if ($nativeKey->rightArrow || $key === 'l') {
                    $setSelectedIndex(function ($idx) use ($visibleNodes, $setExpandedState) {
                        $node = $visibleNodes[$idx] ?? null;
                        if ($node !== null && !empty($node['node']->children)) {
                            $setExpandedState(function ($currentState) use ($node) {
                                $newState = $currentState;
                                $newState[$node['path']] = true;

                                if ($this->onToggle !== null) {
                                    ($this->onToggle)($node['node'], true);
                                }

                                return $newState;
                            });
                        }

                        return $idx;
                    });
                }

                // Collapse with Left arrow
                if ($nativeKey->leftArrow || $key === 'h') {
                    $setSelectedIndex(function ($idx) use ($visibleNodes, $setExpandedState) {
                        $node = $visibleNodes[$idx] ?? null;
                        if ($node !== null && !empty($node['node']->children)) {
                            $setExpandedState(function ($currentState) use ($node) {
                                $newState = $currentState;
                                $newState[$node['path']] = false;

                                if ($this->onToggle !== null) {
                                    ($this->onToggle)($node['node'], false);
                                }

                                return $newState;
                            });
                        }

                        return $idx;
                    });
                }

                // Expand all with '*'
                if ($key === '*') {
                    $newState = [];
                    $this->setAllExpanded($this->nodes, $newState, true);
                    $setExpandedState($newState);
                }

                // Collapse all with '-'
                if ($key === '-') {
                    $newState = [];
                    $this->setAllExpanded($this->nodes, $newState, false);
                    $setExpandedState($newState);
                    // Reset selection to first item since visible nodes will change
                    $setSelectedIndex(0);
                }
            });
        }

        $elements = [];

        if ($this->label !== null) {
            $elements[] = Text::create($this->label)->bold();
        }

        // Show filter input when searchable
        if ($this->searchable) {
            $hasFilter = $filterText !== '';
            if ($hasFilter) {
                $elements[] = Text::create('/ ' . $filterText . '█')->color('cyan');
            } else {
                $elements[] = Text::create($this->filterPlaceholder ?? '')->dim();
            }
        }

        if (empty($visibleNodes) && $filterText !== '') {
            $elements[] = Text::create($this->emptyFilterText ?? 'No matches')->dim();
        }

        $showScrollUp = $range['start'] > 0;
        $showScrollDown = $range['end'] < $nodeCount;

        if ($showScrollUp) {
            $elements[] = Text::create('  ↑ ' . $range['start'] . ' more')->dim();
        }

        // Only render visible items from VirtualList range
        for ($i = $range['start']; $i < $range['end']; $i++) {
            $nodeInfo = $visibleNodes[$i] ?? null;
            if ($nodeInfo !== null) {
                $isFocused = $this->interactive && $i === $selectedIndex;
                $isExpanded = $expandedState[$nodeInfo['path']] ?? $nodeInfo['node']->expanded;
                $isSelected = $this->multiSelect && in_array($nodeInfo['path'], $selectedPaths, true);
                $elements[] = $this->renderTreeNode($nodeInfo, $isFocused, $isExpanded, $isSelected);
            }
        }

        if ($showScrollDown) {
            $hidden = $nodeCount - $range['end'];
            $elements[] = Text::create('  ↓ ' . $hidden . ' more')->dim();
        }

        return Box::column($elements);
    }

    /**
     * @param array<TreeNode> $nodes
     * @param array<bool> $isLastAtDepth
     * @return array<array{node: TreeNode, depth: int, path: string, isLast: bool, isLastAtDepth: array<bool>}>
     */
    private function flattenNodes(array $nodes, bool $forceExpand = false, int $depth = 0, string $pathPrefix = '', array $isLastAtDepth = []): array
    {
        $result = [];

        foreach ($nodes as $index => $node) {
            $isLast = $index === count($nodes) - 1;
            $currentIsLast = array_merge($isLastAtDepth, [$isLast]);
            $path = $pathPrefix . '/' . $index;

            $result[] = [
                'node' => $node,
                'depth' => $depth,
                'path' => $path,
                'isLast' => $isLast,
                'isLastAtDepth' => $currentIsLast,
            ];

            if (!empty($node->children)) {
                $childResult = $this->flattenNodes(
                    $this->normalizeChildren($node->children),
                    $forceExpand,
                    $depth + 1,
                    $path,
                    $currentIsLast,
                );

                foreach ($childResult as $child) {
                    $result[] = $child;
                }
            }
        }

        return $result;
    }

    /**
     * @param array<TreeNode|array{label: string, children?: array<mixed>, expanded?: bool, icon?: string|null, value?: mixed, badge?: string|null}|string> $children
     * @return array<TreeNode>
     */
    private function normalizeChildren(array $children): array
    {
        $result = [];
        foreach ($children as $child) {
            if ($child instanceof TreeNode) {
                $result[] = $child;
            } else {
                $result[] = TreeNode::from($child);
            }
        }

        return $result;
    }

    /**
     * @param array<array{node: TreeNode, depth: int, path: string, isLast: bool, isLastAtDepth: array<bool>}> $flatNodes
     * @param array<string, bool> $expandedState
     * @return array<array{node: TreeNode, depth: int, path: string, isLast: bool, isLastAtDepth: array<bool>}>
     */
    private function getVisibleNodes(array $flatNodes, array $expandedState, string $filterText = ''): array
    {
        $visible = [];
        $hiddenPaths = [];

        foreach ($flatNodes as $nodeInfo) {
            $isHidden = false;
            foreach ($hiddenPaths as $hiddenPath) {
                if (str_starts_with($nodeInfo['path'], $hiddenPath . '/')) {
                    $isHidden = true;
                    break;
                }
            }

            if ($isHidden) {
                continue;
            }

            // Apply filter if set
            if (strlen($filterText) > 0) {
                if ($this->filterFn !== null) {
                    if (!($this->filterFn)($nodeInfo['node'], $filterText)) {
                        continue;
                    }
                } else {
                    // Default: case-insensitive label match
                    if (stripos($nodeInfo['node']->label, $filterText) === false) {
                        continue;
                    }
                }
            }

            $visible[] = $nodeInfo;

            $isExpanded = $expandedState[$nodeInfo['path']] ?? $nodeInfo['node']->expanded ?? $this->expandAll;
            if (!empty($nodeInfo['node']->children) && !$isExpanded) {
                $hiddenPaths[] = $nodeInfo['path'];
            }
        }

        return $visible;
    }

    /**
     * @param array{node: TreeNode, depth: int, path: string, isLast: bool, isLastAtDepth: array<bool>} $nodeInfo
     */
    private function renderTreeNode(array $nodeInfo, bool $isFocused, bool $isExpanded, bool $isSelected = false): mixed
    {
        $node = $nodeInfo['node'];
        $depth = $nodeInfo['depth'];
        $isLastAtDepth = $nodeInfo['isLastAtDepth'];

        $parts = [];

        // Selection indicator for multi-select mode
        if ($this->multiSelect) {
            $checkbox = $isSelected ? '◉ ' : '○ ';
            $checkboxText = Text::create($checkbox);
            if ($isSelected) {
                $checkboxText = $checkboxText->color('green');
            } else {
                $checkboxText = $checkboxText->dim();
            }
            $parts[] = $checkboxText;
        }

        if ($isFocused) {
            $parts[] = Text::create('› ')->color('cyan');
        } else {
            $parts[] = Text::create('  ');
        }

        if ($this->showGuides && $depth > 0) {
            $parts[] = Text::create($this->buildGuides($isLastAtDepth))->dim();
        } elseif ($depth > 0) {
            $parts[] = Text::create(str_repeat(' ', $depth * $this->indentSize));
        }

        $hasChildren = !empty($node->children);

        if ($hasChildren) {
            $expandIcon = $isExpanded ? $this->expandedIcon : $this->collapsedIcon;
            $parts[] = Text::create($expandIcon . ' ')->dim();
        } else {
            $parts[] = Text::create($this->leafIcon . ' ')->dim();
        }

        if ($this->showIcons && $node->icon !== null) {
            $parts[] = Text::create($node->icon . ' ');
        } elseif ($this->showIcons) {
            $defaultIcon = $hasChildren ? $this->folderIcon : $this->fileIcon;
            $parts[] = Text::create($defaultIcon . ' ');
        }

        if ($this->renderNode !== null) {
            $parts[] = ($this->renderNode)($node, $isFocused, $isExpanded);
        } else {
            $labelText = Text::create($node->label);
            if ($isFocused) {
                $labelText = $labelText->bold()->color('cyan');
            } elseif ($isSelected) {
                $labelText = $labelText->color('green');
            }
            $parts[] = $labelText;
        }

        if ($node->badge !== null) {
            $parts[] = Text::create(' (' . $node->badge . ')')->dim();
        }

        return Box::row($parts);
    }

    /**
     * @param array<bool> $isLastAtDepth
     */
    private function buildGuides(array $isLastAtDepth): string
    {
        $guides = '';

        $chars = match ($this->guideStyle) {
            'unicode' => ['│', '├', '└', '─'],
            default => ['|', '+', '`', '-'],
        };

        for ($i = 0; $i < count($isLastAtDepth) - 1; $i++) {
            if ($isLastAtDepth[$i]) {
                $guides .= '  ';
            } else {
                $guides .= $chars[0] . ' ';
            }
        }

        if (!empty($isLastAtDepth)) {
            $isLast = end($isLastAtDepth);
            $guides .= ($isLast ? $chars[2] : $chars[1]) . $chars[3];
        }

        return $guides;
    }

    /**
     * @return array<string, bool>
     */
    private function getInitialExpandedState(): array
    {
        $state = [];

        if ($this->expandAll) {
            $this->setAllExpanded($this->nodes, $state, true);
        } elseif ($this->collapseAll) {
            $this->setAllExpanded($this->nodes, $state, false);
        }

        return $state;
    }

    /**
     * @param array<TreeNode> $nodes
     * @param array<string, bool> $state
     */
    private function setAllExpanded(array $nodes, array &$state, bool $expanded, string $pathPrefix = ''): void
    {
        foreach ($nodes as $index => $node) {
            $path = $pathPrefix . '/' . $index;
            $state[$path] = $expanded;

            if (!empty($node->children)) {
                $this->setAllExpanded($this->normalizeChildren($node->children), $state, $expanded, $path);
            }
        }
    }
}
