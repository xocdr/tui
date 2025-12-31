<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Input;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Input\Form;
use Xocdr\Tui\Widgets\Input\FormField;

class FormTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $form = Form::create();

        $this->assertInstanceOf(Form::class, $form);
    }

    public function testRendersFields(): void
    {
        $widget = $this->createWidget(
            Form::create()
                ->addField('name', 'text', 'Name')
                ->addField('email', 'text', 'Email')
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'Name'));
        $this->assertTrue($this->containsText($output, 'Email'));
    }

    public function testRendersTitle(): void
    {
        $widget = $this->createWidget(
            Form::create()
                ->title('Registration')
                ->addField('name', 'text', 'Name')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Registration'));
    }

    public function testOnSubmitCallback(): void
    {
        $submittedData = null;
        $widget = $this->createWidget(
            Form::create()
                ->addField('name', 'text', 'Name')
                ->showCancel(false)  // Simpler test: just 1 field + 1 submit button
                ->onSubmit(function ($data) use (&$submittedData) {
                    $submittedData = $data;
                })
        );

        $this->renderWidget($widget);

        // Tab to the submit button (from field 0 to button at index 1)
        $this->mockHooks->simulateInput("\t");
        $this->renderWidget($widget);

        // Submit form
        $this->mockHooks->simulateInput("\r");
        $this->renderWidget($widget);

        // Should have submitted (may be empty without actual input)
        $this->assertNotNull($submittedData);
    }

    public function testFieldsFromArray(): void
    {
        $widget = $this->createWidget(
            Form::create()
                ->fields([
                    new FormField('name', 'text', 'Name'),
                    new FormField('email', 'text', 'Email'),
                ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Name'));
    }

    public function testFluentChaining(): void
    {
        $form = Form::create()
            ->title('Settings')
            ->addField('name', 'text', 'Name')
            ->addField('email', 'text', 'Email')
            ->submitLabel('Save')
            ->cancelLabel('Cancel')
            ->showCancel(true)
            ->onSubmit(fn ($d) => null)
            ->onCancel(fn () => null);

        $this->assertInstanceOf(Form::class, $form);
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
