<?php
namespace PivotTable;

require (__DIR__ . '/../src/PivotTable.php');
require (__DIR__ . '/../src/Database.php');
require (__DIR__ . '/../src/Util.php');

class SalesExample
{

    public function __construct()
    {
        $pt = new PivotTable();


        $host = 'localhost';
        $user = 'pt_user';
        $password = 'passwd';
        $database = 'pivot_table';
        
        try {
            $con = Database::getConnection($host, $user, $password, $database);
        } catch (Exception $e) {
            print 'Exception: Unable to obtain a database connection ' .$e->geteMessage();
            return;
        }


        $pivot_column_values_sql = 'SELECT DISTINCT sale_date FROM sale ORDER BY sale_date';
        $pivot_column_values = $con->select($pivot_column_values_sql, array('sale_date'));

        $data_sql  = 'SELECT product_type, sale_date, SUM(amt) AS sum_amt, SUM(qty) AS sum_qty ';
        $data_sql .= 'FROM   sale ';
        $data_sql .= 'GROUP BY product_type, sale_date ';
        $data_sql .= 'ORDER BY product_type, sale_date ';

        $results_data = $con->select($data_sql, array('product_type', 'sale_date', 'sum_amt', 'sum_qty'));

        try {
            $pivot_row = array('product_type' => 'Product Type');
            $pivot_column = array('sale_date' => 'Sale Date');
            $summation_columns = array('sum_qty' => 'Qty','sum_amt' => 'Sales');
            //$summation_columns = array('sum_qty' => 'Qty');
            $a = $pt->summarize($results_data, $pivot_column_values, $pivot_row, $pivot_column, $summation_columns);

            $decorator = array(
              'table' => 'table table-condensed table-striped table-bordered table-text-center',
              'total_row' => 'info',
              'pivot_row' => 'success aleft'
            );
            print '<h2>Product Type by Date Example <small>'.Util::downloadLink('demo1', $a).'</small></h2>';
            $o = $pt->render($a, $decorator, count($summation_columns));
        } catch (Exception $e) {
            print $e->geteMessage();
            return;
        }
        finally {
        if ($con) {
            $con->close();
        }
        }


        print $o;
    }
}

$example = new SalesExample();
