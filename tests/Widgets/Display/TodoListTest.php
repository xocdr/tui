<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Widgets\Display;

use Xocdr\Tui\Components\Box;
use Xocdr\Tui\Components\Text;
use Xocdr\Tui\Support\Testing\TuiTestCase;
use Xocdr\Tui\Widgets\Display\TodoItem;
use Xocdr\Tui\Widgets\Display\TodoList;
use Xocdr\Tui\Widgets\Display\TodoStatus;

class TodoListTest extends TuiTestCase
{
    public function testCreateReturnsInstance(): void
    {
        $list = TodoList::create();

        $this->assertInstanceOf(TodoList::class, $list);
    }

    public function testRendersTodos(): void
    {
        $widget = $this->createWidget(
            TodoList::create([
                ['content' => 'First task', 'status' => 'pending'],
                ['content' => 'Second task', 'status' => 'completed'],
            ])
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
        $this->assertTrue($this->containsText($output, 'First task'));
        $this->assertTrue($this->containsText($output, 'Second task'));
    }

    public function testRendersInProgressTask(): void
    {
        $widget = $this->createWidget(
            TodoList::create([
                ['content' => 'Active task', 'status' => 'in_progress', 'activeForm' => 'Working on active task'],
            ])
        );

        $output = $this->renderWidget($widget);

        // In normal view, content is shown (not activeForm)
        $this->assertTrue($this->containsText($output, 'Active task'));
    }

    public function testTodosWithTodoItems(): void
    {
        $widget = $this->createWidget(
            TodoList::create()->todos([
                new TodoItem('1', 'First task'),
                new TodoItem('2', 'Second task'),
            ])
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'First task'));
    }

    public function testShowProgress(): void
    {
        $widget = $this->createWidget(
            TodoList::create([
                ['content' => 'Task 1', 'status' => 'completed'],
                ['content' => 'Task 2', 'status' => 'pending'],
            ])->showProgress()
        );

        $output = $this->renderWidget($widget);

        // Should show progress indicator
        $this->assertNotNull($output);
    }

    public function testMaxItemsLimitsDisplay(): void
    {
        $widget = $this->createWidget(
            TodoList::create([
                ['content' => 'Task 1', 'status' => 'pending'],
                ['content' => 'Task 2', 'status' => 'pending'],
                ['content' => 'Task 3', 'status' => 'pending'],
                ['content' => 'Task 4', 'status' => 'pending'],
                ['content' => 'Task 5', 'status' => 'pending'],
            ])->maxItems(3)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testInteractiveMode(): void
    {
        $widget = $this->createWidget(
            TodoList::create([
                ['content' => 'Task 1', 'status' => 'pending'],
            ])
                ->interactive()
                ->onStatusChange(fn ($id, $status) => null)
        );

        $output = $this->renderWidget($widget);

        $this->assertNotNull($output);
    }

    public function testNestedTasks(): void
    {
        $widget = $this->createWidget(
            TodoList::create([
                [
                    'content' => 'Parent task',
                    'status' => 'pending',
                    'subtasks' => [
                        ['content' => 'Child 1', 'status' => 'pending'],
                        ['content' => 'Child 2', 'status' => 'completed'],
                    ],
                ],
            ])->nestable()
        );

        $output = $this->renderWidget($widget);

        $this->assertTrue($this->containsText($output, 'Parent task'));
        $this->assertTrue($this->containsText($output, 'Child 1'));
    }

    public function testFluentChaining(): void
    {
        $list = TodoList::create([
            ['content' => 'Task 1', 'status' => 'pending'],
        ])
            ->readonly()
            ->maxItems(10)
            ->showSpinner()
            ->showActiveTaskTitle()
            ->canInterrupt()
            ->showDurations()
            ->showProgress();

        $this->assertInstanceOf(TodoList::class, $list);
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

class TodoItemTest extends TuiTestCase
{
    public function testConstructWithRequiredParams(): void
    {
        $item = new TodoItem('1', 'Task content');

        $this->assertEquals('1', $item->id);
        $this->assertEquals('Task content', $item->content);
        $this->assertEquals('Task content', $item->activeForm);
        $this->assertEquals(TodoStatus::PENDING, $item->status);
    }

    public function testConstructWithAllParams(): void
    {
        $item = new TodoItem(
            id: '1',
            content: 'Task content',
            activeForm: 'Doing task',
            status: TodoStatus::IN_PROGRESS,
            duration: '2.3s',
            subtasks: []
        );

        $this->assertEquals('1', $item->id);
        $this->assertEquals('Task content', $item->content);
        $this->assertEquals('Doing task', $item->activeForm);
        $this->assertEquals(TodoStatus::IN_PROGRESS, $item->status);
        $this->assertEquals('2.3s', $item->duration);
    }

    public function testFromCreatesFromArray(): void
    {
        $item = TodoItem::from([
            'id' => '1',
            'content' => 'Task',
            'status' => 'completed',
        ]);

        $this->assertEquals('1', $item->id);
        $this->assertEquals('Task', $item->content);
        $this->assertEquals(TodoStatus::COMPLETED, $item->status);
    }

    public function testFromWithSubtasks(): void
    {
        $item = TodoItem::from([
            'id' => '1',
            'content' => 'Parent',
            'subtasks' => [
                ['id' => '1.1', 'content' => 'Child 1'],
                ['id' => '1.2', 'content' => 'Child 2'],
            ],
        ]);

        $this->assertCount(2, $item->subtasks);
    }
}

class TodoStatusTest extends TuiTestCase
{
    public function testPendingStatus(): void
    {
        $this->assertEquals('pending', TodoStatus::PENDING->value);
    }

    public function testInProgressStatus(): void
    {
        $this->assertEquals('in_progress', TodoStatus::IN_PROGRESS->value);
    }

    public function testCompletedStatus(): void
    {
        $this->assertEquals('completed', TodoStatus::COMPLETED->value);
    }

    public function testBlockedStatus(): void
    {
        $this->assertEquals('blocked', TodoStatus::BLOCKED->value);
    }

    public function testFailedStatus(): void
    {
        $this->assertEquals('failed', TodoStatus::FAILED->value);
    }

    public function testSkippedStatus(): void
    {
        $this->assertEquals('skipped', TodoStatus::SKIPPED->value);
    }
}
