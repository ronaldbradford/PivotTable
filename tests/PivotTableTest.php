<?php
namespace PivotTable;

require (__DIR__ . '/../src/PivotTable.php');

class PivotTableTest extends \PHPUnit_Framework_TestCase
{

    public function testCanInstantiate()
    {
        $this->assertInstanceOf('\PivotTable\PivotTable', new PivotTable());
    }
}
