<?php

declare(strict_types=1);

namespace Xocdr\Tui\Rendering\Render;

use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\StatefulComponent;
use Xocdr\Tui\Contracts\HooksAwareInterface;
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
     *
     * @throws \RuntimeException If rendering fails
     */
    public function render(Component|callable $component): NodeInterface
    {
        // Start a new render cycle
        RenderCycleTracker::beginCycle();

        try {
            if (is_callable($component)) {
                $result = $component();
            } else {
                $result = $component;
            }

            $node = $this->toNode($result);

            // Finalize the render cycle - suspend effects for components not rendered
            RenderCycleTracker::endCycle();

            return $node;
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            // Re-throw known exceptions
            throw $e;
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                'Component rendering failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
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
        if ($value instanceof \Xocdr\Tui\Ext\ContainerNode) {
            return $this->wrapNativeBox($value);
        }

        if ($value instanceof \Xocdr\Tui\Ext\ContentNode) {
            return $this->wrapNativeText($value);
        }

        // StatefulComponent - compile directly (already returns TuiNode)
        if ($value instanceof StatefulComponent) {
            // Prepare hook context for HooksAware components before compiling
            if ($value instanceof HooksAwareInterface) {
                $value->prepareRender();
                // Track this component as rendered in current cycle
                RenderCycleTracker::trackComponent($value);
            }
            $compiled = $value->toNode();

            return $this->toNode($compiled);
        }

        // Component - compile it
        if ($value instanceof Component) {
            // Prepare hook context for HooksAware components before compiling
            if ($value instanceof HooksAwareInterface) {
                $value->prepareRender();
                // Track this component as rendered in current cycle
                RenderCycleTracker::trackComponent($value);
            }
            $compiled = $value->toNode();

            return $this->toNode($compiled);
        }

        // Array format (legacy support)
        if (is_array($value)) {
            /** @var array<string, mixed> $value */
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
     * Validates array structure to ensure type safety.
     *
     * @param array<string, mixed> $data
     *
     * @throws \InvalidArgumentException If array structure is invalid
     */
    private function arrayToNode(array $data): NodeInterface
    {
        $type = $data['type'] ?? 'box';

        if (!is_string($type)) {
            throw new \InvalidArgumentException(
                'Render array "type" must be a string, got ' . get_debug_type($type)
            );
        }

        if ($type === 'text') {
            $content = $data['content'] ?? '';
            if (!is_string($content)) {
                throw new \InvalidArgumentException(
                    'Text render array "content" must be a string, got ' . get_debug_type($content)
                );
            }

            $textStyle = $data['style'] ?? [];
            if (!is_array($textStyle)) {
                throw new \InvalidArgumentException(
                    'Render array "style" must be an array, got ' . get_debug_type($textStyle)
                );
            }

            /** @var array<string, mixed> $textStyle */
            return $this->target->createText($content, $textStyle);
        }

        // Box type
        $boxStyle = $data['style'] ?? [];
        if (!is_array($boxStyle)) {
            throw new \InvalidArgumentException(
                'Render array "style" must be an array, got ' . get_debug_type($boxStyle)
            );
        }

        /** @var array<string, mixed> $boxStyle */
        $box = $this->target->createBox($boxStyle);

        if (isset($data['children'])) {
            if (!is_array($data['children'])) {
                throw new \InvalidArgumentException(
                    'Render array "children" must be an array, got ' . get_debug_type($data['children'])
                );
            }

            foreach ($data['children'] as $child) {
                $childNode = $this->toNode($child);
                $box->addChild($childNode);
            }
        }

        return $box;
    }

    /**
     * Wrap a native ContainerNode in a NodeInterface.
     */
    private function wrapNativeBox(\Xocdr\Tui\Ext\ContainerNode $native): NodeInterface
    {
        return new NativeBoxWrapper($native);
    }

    /**
     * Wrap a native ContentNode in a NodeInterface.
     */
    private function wrapNativeText(\Xocdr\Tui\Ext\ContentNode $native): NodeInterface
    {
        return new NativeTextWrapper($native);
    }
}
