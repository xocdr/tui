<?php

declare(strict_types=1);

namespace Xocdr\Tui\Rendering\Render;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\StatefulComponent;
use Xocdr\Tui\Contracts\NodeInterface;
use Xocdr\Tui\Contracts\RendererInterface;
use Xocdr\Tui\Contracts\RenderTargetInterface;

/**
 * Renders component trees to node trees.
 *
 * Extracted from Instance class to follow Single Responsibility Principle.
 */
class ComponentRenderer implements RendererInterface
{
    public function __construct(
        private RenderTargetInterface $target
    ) {
    }

    /**
     * Render a component or callable to a node tree.
     */
    public function render(Component|callable $component): NodeInterface
    {
        if (is_callable($component)) {
            $result = $component();
        } else {
            $result = $component;
        }

        return $this->toNode($result);
    }

    /**
     * Convert any value to a node.
     */
    public function toNode(mixed $value): NodeInterface
    {
        // Already a node
        if ($value instanceof NodeInterface) {
            return $value;
        }

        // Native extension objects - wrap them
        if ($value instanceof \Xocdr\Tui\Ext\Box) {
            return $this->wrapNativeBox($value);
        }

        if ($value instanceof \Xocdr\Tui\Ext\Text) {
            return $this->wrapNativeText($value);
        }

        // StatefulComponent - render directly (already returns TuiBox/TuiText)
        if ($value instanceof StatefulComponent) {
            $rendered = $value->render();

            return $this->toNode($rendered);
        }

        // Component - render it
        if ($value instanceof Component) {
            $rendered = $value->render();

            return $this->toNode($rendered);
        }

        // Array format (legacy support)
        if (is_array($value)) {
            return $this->arrayToNode($value);
        }

        // String - create text node
        if (is_string($value)) {
            return $this->target->createText($value);
        }

        throw new \RuntimeException(
            'Cannot convert value to node: ' . get_debug_type($value)
        );
    }

    /**
     * Convert a render array to a node.
     *
     * @param array<string, mixed> $data
     */
    private function arrayToNode(array $data): NodeInterface
    {
        $type = $data['type'] ?? 'box';

        if ($type === 'text') {
            /** @var string $content */
            $content = $data['content'] ?? '';
            /** @var array<string, mixed> $textStyle */
            $textStyle = $data['style'] ?? [];

            return $this->target->createText($content, $textStyle);
        }

        // Box type
        /** @var array<string, mixed> $boxStyle */
        $boxStyle = $data['style'] ?? [];
        $box = $this->target->createBox($boxStyle);

        if (isset($data['children']) && is_array($data['children'])) {
            foreach ($data['children'] as $child) {
                $childNode = $this->toNode($child);
                $box->addChild($childNode);
            }
        }

        return $box;
    }

    /**
     * Wrap a native TuiBox in a NodeInterface.
     */
    private function wrapNativeBox(\Xocdr\Tui\Ext\Box $native): NodeInterface
    {
        return new NativeBoxWrapper($native);
    }

    /**
     * Wrap a native TuiText in a NodeInterface.
     */
    private function wrapNativeText(\Xocdr\Tui\Ext\Text $native): NodeInterface
    {
        return new NativeTextWrapper($native);
    }
}
