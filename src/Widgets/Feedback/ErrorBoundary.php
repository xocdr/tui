<?php

declare(strict_types=1);

namespace Xocdr\Tui\Widgets\Feedback;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Component;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Widgets\Widget;

class ErrorBoundary extends Widget
{
    private mixed $children = null;

    private mixed $fallback = null;

    /** @var callable|null */
    private $onError = null;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function children(mixed $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function fallback(mixed $fallback): self
    {
        $this->fallback = $fallback;

        return $this;
    }

    public function onError(callable $callback): self
    {
        $this->onError = $callback;

        return $this;
    }

    public function build(): Component
    {
        $hooks = $this->hooks();

        [$error, $setError] = $hooks->state(null);

        if ($error !== null) {
            if ($this->fallback !== null) {
                if (is_callable($this->fallback)) {
                    return ($this->fallback)($error);
                }

                return $this->fallback;
            }

            return $this->renderDefaultFallback($error);
        }

        try {
            if (is_callable($this->children)) {
                return ($this->children)();
            }

            return $this->children;
        } catch (\Throwable $e) {
            $setError($e);

            if ($this->onError !== null) {
                ($this->onError)($e);
            }

            if ($this->fallback !== null) {
                if (is_callable($this->fallback)) {
                    return ($this->fallback)($e);
                }

                return $this->fallback;
            }

            return $this->renderDefaultFallback($e);
        }
    }

    private function renderDefaultFallback(\Throwable $error): mixed
    {
        return Box::create()
            ->border('round')
            ->borderColor('red')
            ->padding(1)
            ->children([
                Box::column([
                    Text::create('⚠ Error')->bold()->color('red'),
                    Text::create(''),
                    Text::create(get_class($error))->bold(),
                    Text::create($error->getMessage()),
                    Text::create(''),
                    Text::create($error->getFile() . ':' . $error->getLine())->dim(),
                ]),
            ]);
    }
}
