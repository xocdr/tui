<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\BoxColumn;
use Xocdr\Tui\Components\BoxRow;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class KeyHint extends Widget
{
    /** @var array<array{key: string, action: string, group?: string}> */
    private array $hints = [];

    private string $separator = '  ';

    private bool $compact = false;

    private string $keyColor = 'cyan';

    private string $actionColor = 'white';

    private bool $keyBold = true;

    private bool $keyInverse = false;

    private string $keyPrefix = '[';

    private string $keySuffix = ']';

    private string $displayMode = 'inline';

    private int $columns = 2;

    private int $columnWidth = 30;

    private bool $showGroupHeaders = true;

    private string $groupHeaderColor = 'yellow';

    private function __construct()
    {
    }

    /**
     * @param string|array<array{key: string, action: string}> $keyOrHints
     */
    public static function create(string|array $keyOrHints = [], ?string $action = null): self
    {
        $instance = new self();

        if (is_string($keyOrHints) && $action !== null) {
            $instance->hints = [['key' => $keyOrHints, 'action' => $action]];
        } elseif (is_array($keyOrHints)) {
            $instance->hints($keyOrHints);
        }

        return $instance;
    }

    /**
     * @param array<mixed> $hints Array of hint definitions
     */
    public function hints(array $hints): self
    {
        $this->hints = [];
        foreach ($hints as $hint) {
            if (is_array($hint) && isset($hint['key']) && isset($hint['action'])) {
                $this->hints[] = [
                    'key' => (string) $hint['key'],
                    'action' => (string) $hint['action'],
                    'group' => isset($hint['group']) ? (string) $hint['group'] : null,
                ];
            }
        }

        return $this;
    }

    public function add(string $key, string $action, ?string $group = null): self
    {
        $this->hints[] = ['key' => $key, 'action' => $action, 'group' => $group];

        return $this;
    }

    public function key(string $key): self
    {
        if (!empty($this->hints)) {
            $this->hints[count($this->hints) - 1]['key'] = $key;
        } else {
            $this->hints[] = ['key' => $key, 'action' => ''];
        }

        return $this;
    }

    public function action(string $action): self
    {
        if (!empty($this->hints)) {
            $this->hints[count($this->hints) - 1]['action'] = $action;
        } else {
            $this->hints[] = ['key' => '', 'action' => $action];
        }

        return $this;
    }

    public function separator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    public function compact(bool $compact = true): self
    {
        $this->compact = $compact;

        return $this;
    }

    public function keyColor(string $color): self
    {
        $this->keyColor = $color;

        return $this;
    }

    public function actionColor(string $color): self
    {
        $this->actionColor = $color;

        return $this;
    }

    public function keyBold(bool $bold = true): self
    {
        $this->keyBold = $bold;

        return $this;
    }

    public function keyInverse(bool $inverse = true): self
    {
        $this->keyInverse = $inverse;

        return $this;
    }

    public function keyPrefix(string $prefix): self
    {
        $this->keyPrefix = $prefix;

        return $this;
    }

    public function keySuffix(string $suffix): self
    {
        $this->keySuffix = $suffix;

        return $this;
    }

    public function noBrackets(): self
    {
        $this->keyPrefix = '';
        $this->keySuffix = '';

        return $this;
    }

    /**
     * Set display mode: 'inline' (default), 'grid', or 'grouped'.
     */
    public function displayMode(string $mode): self
    {
        $this->displayMode = $mode;

        return $this;
    }

    /**
     * Shorthand for displayMode('inline').
     */
    public function inline(): self
    {
        $this->displayMode = 'inline';

        return $this;
    }

    /**
     * Shorthand for displayMode('grid').
     */
    public function grid(int $columns = 2): self
    {
        $this->displayMode = 'grid';
        $this->columns = $columns;

        return $this;
    }

    /**
     * Shorthand for displayMode('grouped') - displays hints grouped with headers.
     */
    public function grouped(): self
    {
        $this->displayMode = 'grouped';

        return $this;
    }

    public function columns(int $columns): self
    {
        $this->columns = max(1, $columns);

        return $this;
    }

    public function columnWidth(int $width): self
    {
        $this->columnWidth = max(10, $width);

        return $this;
    }

    public function showGroupHeaders(bool $show = true): self
    {
        $this->showGroupHeaders = $show;

        return $this;
    }

    public function groupHeaderColor(string $color): self
    {
        $this->groupHeaderColor = $color;

        return $this;
    }

    public function build(): Component
    {
        if (empty($this->hints)) {
            return new Text('');
        }

        return match ($this->displayMode) {
            'grid' => $this->buildGrid(),
            'grouped' => $this->buildGrouped(),
            default => $this->buildInline(),
        };
    }

    private function buildInline(): Component
    {
        $parts = [];

        foreach ($this->hints as $i => $hint) {
            if ($i > 0) {
                $parts[] = new Text($this->separator);
            }

            $parts[] = $this->renderHint($hint);
        }

        return new BoxRow($parts);
    }

    private function buildGrid(): Component
    {
        $rows = [];
        $chunks = array_chunk($this->hints, $this->columns);

        foreach ($chunks as $row) {
            $cols = [];
            foreach ($row as $hint) {
                $cols[] = new Box()
                    ->width($this->columnWidth)
                    ->child($this->renderHint($hint));
            }
            $rows[] = new BoxRow($cols);
        }

        return new BoxColumn($rows);
    }

    private function buildGrouped(): Component
    {
        // Group hints by their group property
        $groups = [];
        $ungrouped = [];

        foreach ($this->hints as $hint) {
            $group = $hint['group'] ?? null;
            if ($group !== null) {
                if (!isset($groups[$group])) {
                    $groups[$group] = [];
                }
                $groups[$group][] = $hint;
            } else {
                $ungrouped[] = $hint;
            }
        }

        $elements = [];

        // Render grouped hints
        foreach ($groups as $groupName => $hints) {
            if ($this->showGroupHeaders) {
                $elements[] = new Text($groupName)->bold()->color($this->groupHeaderColor);
            }

            $chunks = array_chunk($hints, $this->columns);
            foreach ($chunks as $row) {
                $cols = [];
                foreach ($row as $hint) {
                    $cols[] = new Box()
                        ->width($this->columnWidth)
                        ->child($this->renderHint($hint));
                }
                $elements[] = new BoxRow($cols);
            }

            // Add spacing between groups
            $elements[] = new Text('');
        }

        // Render ungrouped hints at the end
        if (!empty($ungrouped)) {
            if ($this->showGroupHeaders && !empty($groups)) {
                $elements[] = new Text('Other')->bold()->color($this->groupHeaderColor);
            }

            $chunks = array_chunk($ungrouped, $this->columns);
            foreach ($chunks as $row) {
                $cols = [];
                foreach ($row as $hint) {
                    $cols[] = new Box()
                        ->width($this->columnWidth)
                        ->child($this->renderHint($hint));
                }
                $elements[] = new BoxRow($cols);
            }
        }

        return new BoxColumn($elements);
    }

    /**
     * @param array{key: string, action: string} $hint
     */
    private function renderHint(array $hint): mixed
    {
        $key = $hint['key'];
        $action = $hint['action'];

        if ($this->compact) {
            $keyText = new Text($key);
            if ($this->keyBold) {
                $keyText = $keyText->bold();
            }
            $keyText = $keyText->color($this->keyColor);

            return new BoxRow([
                $keyText,
                new Text(':'),
                new Text($action)->color($this->actionColor),
            ]);
        }

        $keyDisplay = $this->keyPrefix . $key . $this->keySuffix;
        $keyText = new Text($keyDisplay);

        if ($this->keyBold) {
            $keyText = $keyText->bold();
        }

        if ($this->keyInverse) {
            $keyText = $keyText->inverse();
        } else {
            $keyText = $keyText->color($this->keyColor);
        }

        return new BoxRow([
            $keyText,
            new Text(' '),
            new Text($action)->color($this->actionColor),
        ]);
    }
}
