<?php

namespace OneSheetTest;

use OneSheet\Sheet;
use OneSheet\Style\Style;
use OneSheet\CellBuilder;
use OneSheet\Width\WidthCalculator;
use OneSheet\Width\WidthCollection;

class SheetTest extends \PHPUnit_Framework_TestCase
{
    public function testAddRow()
    {
        $sheet = new Sheet(new CellBuilder(), new WidthCalculator(new WidthCollection()));
        $expectedXml = '<row r="1" spans="1:3"><c r="A1" s="0" t="inlineStr"><is><t>Heinz</t></is></c><c r="B1" s="0" t="inlineStr"><is><t>Becker &amp; Decker</t></is></c><c r="C1" s="0" t="b"><v>1</v></c></row>';
        $xml = $sheet->addRow(array('Heinz', 'Becker & Decker', true), new Style());
        $this->assertEquals($expectedXml, $xml);
    }

    public function testEnableAutoSizing()
    {
        $sheet = new Sheet(new CellBuilder(), new WidthCalculator(new WidthCollection()));
        $this->assertCount(0, $sheet->getColumnWidths());

        $sheet->enableCellAutosizing();
        $sheet->addRow(array(1,2,3), new Style());
        $sheet->disableCellAutosizing();
        $sheet->addRow(array(1,2,3,4,5,6,7), new Style());
        $this->assertCount(3, $sheet->getColumnWidths());
    }

    public function testGetFreezePaneXml()
    {
        $sheet = new Sheet(new CellBuilder(), new WidthCalculator(new WidthCollection()));
        $this->assertEquals('', $sheet->getFreezePaneXml());

        $sheet->setFreezePaneCellId('A5');
        $expectedXml = '<pane ySplit="4" topLeftCell="A5" activePane="bottomLeft" state="frozen"/>';
        $this->assertEquals($expectedXml, $sheet->getFreezePaneXml());
    }

    public function testWidthCalculation()
    {
        $sheet = new Sheet(new CellBuilder(), new WidthCalculator(new WidthCollection()));
        $sheet->addRow(array(1,2,3), new Style());
        $this->assertEmpty($sheet->getColumnWidths());

        $sheet->enableCellAutosizing();
        $sheet->addRow(array(1,2,3), new Style());
        $this->assertNotEmpty($sheet->getColumnWidths());

        $widthSum = array_sum($sheet->getColumnWidths());
        $largerStyle = new Style();
        $largerStyle->setFontName('Something')->setFontSize(15);
        $sheet->addRow(array(1,2,3), $largerStyle);
        $this->assertGreaterThan($widthSum, array_sum($sheet->getColumnWidths()));
    }

    public function testSetFixedColumnWidths()
    {
        $sheet = new Sheet(new CellBuilder(), new WidthCalculator(new WidthCollection()));
        $sheet->setFixedColumnWidths(array(0 => 10, 3 => 20, 7 => 10));
        $this->assertEquals(40, array_sum($sheet->getColumnWidths()));

        $sheet->setFixedColumnWidths(array(3 => 10));
        $this->assertEquals(30, array_sum($sheet->getColumnWidths()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetFixedColumnWidthsWithInvalidValues()
    {
        $sheet = new Sheet(new CellBuilder(), new WidthCalculator(new WidthCollection()));
        $sheet->setFixedColumnWidths(array(1 => 'abc', 7 => 10));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetFixedColumnWidthsWithInvalidKeys()
    {
        $sheet = new Sheet(new CellBuilder(), new WidthCalculator(new WidthCollection()));
        $sheet->setFixedColumnWidths(array('A1' => 10, 7 => 10));
    }
}
