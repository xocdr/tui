<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Feedback;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Feedback\LoadingState;

class LoadingStateTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $state = LoadingState::create();

        $this->assertInstanceOf(LoadingState::class, $state);
    }

    public function testRendersLoadingMessage(): void
    {
        $widget = $this->createWidget(LoadingState::loading('Fetching data...'));

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Fetching data...'));
    }

    public function testSuccessState(): void
    {
        $widget = $this->createWidget(LoadingState::success('Data loaded'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Data loaded'));
        $this->assertTrue($this->containsText($output, '✓'));
    }

    public function testErrorState(): void
    {
        $widget = $this->createWidget(LoadingState::error('Failed to load'));

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Failed to load'));
        $this->assertTrue($this->containsText($output, '✗'));
    }

    public function testStateCanBeChanged(): void
    {
        $widget = $this->createWidget(
            LoadingState::create()
                ->state('loading')
                ->message('Processing...')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Processing...'));
    }

    public function testSuccessMessageOverridesDefault(): void
    {
        $widget = $this->createWidget(
            LoadingState::create()
                ->state('success')
                ->message('Default')
                ->successMessage('All done!')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'All done!'));
    }

    public function testErrorMessageOverridesDefault(): void
    {
        $widget = $this->createWidget(
            LoadingState::create()
                ->state('error')
                ->message('Default')
                ->errorMessage('Oops!')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Oops!'));
    }

    public function testChildrenShownOnSuccess(): void
    {
        $widget = $this->createWidget(
            LoadingState::create()
                ->state('success')
                ->message('Done')
                ->children('Content loaded successfully')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Content loaded successfully'));
    }

    public function testLoadingContentShownDuringLoading(): void
    {
        $widget = $this->createWidget(
            LoadingState::create()
                ->state('loading')
                ->message('Loading')
                ->loadingContent('Preparing files...')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Preparing files...'));
    }

    public function testFluentChaining(): void
    {
        $state = LoadingState::create()
            ->state('loading')
            ->message('Loading...')
            ->successMessage('Done!')
            ->errorMessage('Failed')
            ->spinnerType('dots')
            ->showState(true)
            ->children('Content')
            ->loadingContent('Please wait...')
            ->successContent('Success content')
            ->errorContent('Error details');

        $this->assertInstanceOf(LoadingState::class, $state);
    }

    /**
     * Collect all text content from a component tree.
     */
    private function collectTextContent(mixed $component): array
    {
        $texts = [];

        if ($component instanceof Text) {
            $texts[] = $component->getContent();
        } elseif ($component instanceof Box) {
            foreach ($component->getChildren() as $child) {
                $texts = array_merge($texts, $this->collectTextContent($child));
            }
        }

        return $texts;
    }

    /**
     * Check if component tree contains text.
     */
    private function containsText(mixed $component, string $needle): bool
    {
        foreach ($this->collectTextContent($component) as $text) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }
        return false;
    }
}
