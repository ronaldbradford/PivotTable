<?php
namespace PivotTable;

require (__DIR__ . '/../src/Database.php');

class DatabaseTest extends \PHPUnit_Framework_TestCase
{

    const DEFAULT_HOST = 'localhost';
    const DEFAULT_USER = 'pt_user';
    const DEFAULT_PASSWORD = 'passwd';
    const DEFAULT_DATABASE = 'pivot_table';

    /**
     * @expectedException Exception
     */
    public function testInvalidConnection()
    {
        $host = '';
        $user = '';
        $password = '';
        $database = '';

        Database::getConnection($host, $user, $password, $database);
    }
  
    public static function getDefaultConnection()
    {
        return Database::getConnection(
            self::DEFAULT_HOST,
            self::DEFAULT_USER,
            self::DEFAULT_PASSWORD,
            self::DEFAULT_DATABASE
        );
    }

    public function testGetConnection()
    {
        $con = self::getDefaultConnection();
        $this->assertInstanceOf('\PivotTable\Database', $con);
        $this->assertTrue($con instanceof Database);
        $con->close();
    }


    public function testSingleRowSingleColumnResult()
    {
        $con = self::getDefaultConnection();
        $expectedString = 'Hello World';
        $results = $con->select('SELECT "'.$expectedString.'" AS one', array('one'));
        $this->assertEquals(count($results), 1);
        $this->assertEquals($results[0]['one'], $expectedString);
        $con->close();
    }
}
