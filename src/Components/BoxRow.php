<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

/**
 * Row container component (flexDirection: row).
 *
 * A convenience wrapper around Box with row direction pre-set.
 *
 * @example Fluent append with explicit keys:
 * (new BoxRow)
 *     ->append($widget1, 'my-widget-1')
 *     ->append($widget2, 'my-widget-2')
 *
 * @example Associative array (explicit keys for widget caching):
 * new BoxRow([
 *     'my-widget' => $widget,
 *     'my-list' => $todoList,
 * ])
 */
class BoxRow extends Box
{
    /**
     * Create a new BoxRow instance.
     *
     * Supports associative arrays where string keys become widget keys
     * for instance persistence across re-renders.
     *
     * @param array<int|string, Component|string> $children Initial children
     */
    public function __construct(array $children = [])
    {
        parent::__construct($children);
        $this->flexDirection('row');
    }

    /**
     * Add a column container as a child and return it for chaining.
     *
     * @param array<Component|string>|string|null $childrenOrKey Array of children, or optional key
     * @param string|null $key Optional key when first param is an array
     * @return BoxColumn The new column Box (for chaining children onto it)
     */
    public function addColumn(array|string|null $childrenOrKey = null, ?string $key = null): BoxColumn
    {
        $column = new BoxColumn();

        if (is_array($childrenOrKey)) {
            foreach ($childrenOrKey as $child) {
                $column->append($child);
            }
            $this->append($column, $key);
        } else {
            $this->append($column, $childrenOrKey);
        }

        return $column;
    }

    /**
     * Add a nested row container as a child and return it for chaining.
     *
     * @param array<Component|string>|string|null $childrenOrKey Array of children, or optional key
     * @param string|null $key Optional key when first param is an array
     * @return BoxRow The new row Box (for chaining children onto it)
     */
    public function addRow(array|string|null $childrenOrKey = null, ?string $key = null): BoxRow
    {
        $row = new BoxRow();

        if (is_array($childrenOrKey)) {
            foreach ($childrenOrKey as $child) {
                $row->append($child);
            }
            $this->append($row, $key);
        } else {
            $this->append($row, $childrenOrKey);
        }

        return $row;
    }
}
