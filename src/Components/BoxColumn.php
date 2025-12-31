<?php

declare(strict_types=1);

namespace Xocdr\Tui\Components;

/**
 * Column container component (flexDirection: column).
 *
 * A convenience wrapper around Box with column direction pre-set.
 *
 * @example
 * $column = new BoxColumn();
 * $column->append(new Text('First'))
 *        ->append(new Text('Second'));
 *
 * // Or with children in constructor
 * $column = new BoxColumn([
 *     new Text('First'),
 *     new Text('Second'),
 * ]);
 */
class BoxColumn extends Box
{
    /**
     * Create a new BoxColumn instance.
     *
     * @param array<Component|string> $children Initial children
     */
    public function __construct(array $children = [])
    {
        parent::__construct($children);
        $this->flexDirection('column');
    }

    /**
     * Add a row container as a child and return it for chaining.
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

    /**
     * Add a nested column container as a child and return it for chaining.
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
}
