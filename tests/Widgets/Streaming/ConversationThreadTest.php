<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Streaming;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Streaming\ConversationMessage;
use Xocdr\Tui\Widgets\Streaming\ConversationThread;

class ConversationThreadTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $widget = ConversationThread::create();

        $this->assertInstanceOf(ConversationThread::class, $widget);
    }

    public function testRendersEmptyState(): void
    {
        $widget = $this->createWidget(ConversationThread::create());

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'No messages yet'));
    }

    public function testRendersUserMessage(): void
    {
        $widget = $this->createWidget(
            ConversationThread::create()
                ->addUserMessage('Hello, assistant!')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Hello, assistant!'));
        $this->assertTrue($this->containsText($output, 'You'));
    }

    public function testRendersAssistantMessage(): void
    {
        $widget = $this->createWidget(
            ConversationThread::create()
                ->addAssistantMessage('Hello! How can I help?')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Hello! How can I help?'));
        $this->assertTrue($this->containsText($output, 'Assistant'));
    }

    public function testRendersMultipleMessages(): void
    {
        $widget = $this->createWidget(
            ConversationThread::create()
                ->addUserMessage('What is 2+2?')
                ->addAssistantMessage('2+2 equals 4.')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'What is 2+2?'));
        $this->assertTrue($this->containsText($output, '2+2 equals 4.'));
    }

    public function testCustomLabels(): void
    {
        $widget = $this->createWidget(
            ConversationThread::create()
                ->userLabel('Human')
                ->assistantLabel('AI')
                ->addUserMessage('Test')
                ->addAssistantMessage('Response')
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Human'));
        $this->assertTrue($this->containsText($output, 'AI'));
    }

    public function testMessagesFromArray(): void
    {
        $widget = $this->createWidget(
            ConversationThread::create()
                ->messages([
                    ['role' => 'user', 'content' => 'First message'],
                    ['role' => 'assistant', 'content' => 'Second message'],
                ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'First message'));
        $this->assertTrue($this->containsText($output, 'Second message'));
    }

    public function testMessagesFromMessageObjects(): void
    {
        $widget = $this->createWidget(
            ConversationThread::create()
                ->messages([
                    ConversationMessage::user('User says'),
                    ConversationMessage::assistant('Assistant replies'),
                ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'User says'));
        $this->assertTrue($this->containsText($output, 'Assistant replies'));
    }

    public function testAvatarsCanBeHidden(): void
    {
        $widget = $this->createWidget(
            ConversationThread::create()
                ->showAvatars(false)
                ->addUserMessage('Test')
        );

        $output = $this->renderWidget($widget);

        $this->assertFalse($this->containsText($output, '=d'));
    }

    public function testFluentChaining(): void
    {
        $widget = ConversationThread::create()
            ->messages([])
            ->showTimestamps(true)
            ->showAvatars(true)
            ->userLabel('User')
            ->assistantLabel('Bot')
            ->userColor('blue')
            ->assistantColor('green')
            ->maxWidth(80)
            ->alternateBackground(true);

        $this->assertInstanceOf(ConversationThread::class, $widget);
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
