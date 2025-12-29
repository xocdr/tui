<?php

declare(strict_types=1);

namespace Xocdr\Tui\Tests\Components;

use PHPUnit\Framework\TestCase;
use Xocdr\Tui\Widgets\Table;

class TableTest extends TestCase
{
    public function testCreate(): void
    {
        $table = Table::create(['Name', 'Age']);

        $this->assertSame(['Name', 'Age'], $table->getHeaders());
        $this->assertEmpty($table->getRows());
    }

    public function testAddRow(): void
    {
        $table = Table::create(['Name', 'Age'])
            ->addRow(['Alice', 30])
            ->addRow(['Bob', 25]);

        $rows = $table->getRows();

        $this->assertCount(2, $rows);
        $this->assertSame(['Alice', '30'], $rows[0]);
        $this->assertSame(['Bob', '25'], $rows[1]);
    }

    public function testAddRows(): void
    {
        $table = Table::create(['Name', 'Age'])
            ->addRows([
                ['Alice', 30],
                ['Bob', 25],
            ]);

        $this->assertCount(2, $table->getRows());
    }

    public function testHeaders(): void
    {
        $table = Table::create()
            ->headers(['Col1', 'Col2', 'Col3']);

        $this->assertSame(['Col1', 'Col2', 'Col3'], $table->getHeaders());
    }

    public function testGetColumnCount(): void
    {
        $table = Table::create(['A', 'B', 'C']);

        $this->assertSame(3, $table->getColumnCount());

        // With row having more columns
        $table->addRow(['1', '2', '3', '4']);
        $this->assertSame(4, $table->getColumnCount());
    }

    public function testGetColumnWidths(): void
    {
        $table = Table::create(['Name', 'Age'])
            ->addRow(['Alice', 30])
            ->addRow(['Bob', 25]);

        $widths = $table->getColumnWidths();

        $this->assertSame(5, $widths[0]); // 'Alice' is longest
        $this->assertSame(3, $widths[1]); // 'Age' is longest
    }

    public function testSetAlign(): void
    {
        $table = Table::create(['Name', 'Amount'])
            ->setAlign(1, true) // Right-align Amount
            ->addRow(['Alice', 100]);

        // Just verify it doesn't throw
        $lines = $table->render();
        $this->assertNotEmpty($lines);
    }

    public function testBorder(): void
    {
        $table = Table::create(['A'])
            ->border('double')
            ->addRow(['1']);

        $lines = $table->render();

        // Double border uses different characters
        $this->assertNotEmpty($lines);
    }

    public function testHideHeader(): void
    {
        $table = Table::create(['Name'])
            ->hideHeader()
            ->addRow(['Alice']);

        $lines = $table->render();

        // Should have fewer lines without header
        $withHeader = Table::create(['Name'])->addRow(['Alice'])->render();
        $this->assertLessThan(count($withHeader), count($lines));
    }

    public function testRender(): void
    {
        $table = Table::create(['Name', 'Age'])
            ->addRow(['Alice', 30])
            ->addRow(['Bob', 25]);

        $lines = $table->render();

        // Should have: top border, header, separator, 2 data rows, bottom border
        $this->assertCount(6, $lines);

        // Check for border characters
        $this->assertStringContainsString('─', $lines[0]); // Top border
        $this->assertStringContainsString('│', $lines[1]); // Header row
    }

    public function testToString(): void
    {
        $table = Table::create(['A'])
            ->addRow(['1']);

        $string = $table->toString();

        $this->assertIsString($string);
        $this->assertStringContainsString('A', $string);
        $this->assertStringContainsString('1', $string);
    }

    public function testToText(): void
    {
        $table = Table::create(['A'])
            ->addRow(['1']);

        $text = $table->toText();

        $this->assertInstanceOf(\Xocdr\Tui\Components\Text::class, $text);
    }

    public function testFluentInterface(): void
    {
        $table = Table::create()
            ->headers(['A', 'B'])
            ->addRow(['1', '2'])
            ->setAlign(0, false)
            ->border('single')
            ->borderColor('#ffffff')
            ->headerColor('#00ff00');

        $this->assertInstanceOf(Table::class, $table);
    }

    public function testEmptyTable(): void
    {
        $table = Table::create(['A', 'B', 'C']);

        $lines = $table->render();

        // Should still render borders and headers
        $this->assertNotEmpty($lines);
    }

    public function testNumericValues(): void
    {
        $table = Table::create(['Value'])
            ->addRow([42])
            ->addRow([3.14])
            ->addRow([-10]);

        $rows = $table->getRows();

        $this->assertSame('42', $rows[0][0]);
        $this->assertSame('3.14', $rows[1][0]);
        $this->assertSame('-10', $rows[2][0]);
    }
}
