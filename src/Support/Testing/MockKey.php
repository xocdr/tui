<?php

declare(strict_types=1);

namespace Xocdr\Tui\Support\Testing;

/**
 * Mock Key for testing without the C extension.
 *
 * Mimics the structure of the native Xocdr\Tui\Ext\Key class.
 */
class MockKey
{
    public function __construct(
        public string $key,
        public string $name = '',
        public bool $ctrl = false,
        public bool $alt = false,
        public bool $shift = false,
        public bool $meta = false
    ) {
        if ($this->name === '') {
            $this->name = $this->key;
        }
    }

    /**
     * Create a mock key from a character.
     */
    public static function fromChar(string $char, array $modifiers = []): self
    {
        return new self(
            key: $char,
            name: $char,
            ctrl: in_array('ctrl', $modifiers, true),
            alt: in_array('alt', $modifiers, true),
            shift: in_array('shift', $modifiers, true),
            meta: in_array('meta', $modifiers, true)
        );
    }

    /**
     * Create a named key (like arrow keys).
     */
    public static function named(string $name, array $modifiers = []): self
    {
        return new self(
            key: '',
            name: $name,
            ctrl: in_array('ctrl', $modifiers, true),
            alt: in_array('alt', $modifiers, true),
            shift: in_array('shift', $modifiers, true),
            meta: in_array('meta', $modifiers, true)
        );
    }
}
