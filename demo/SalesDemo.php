<?php
namespace PivotTable;

require (__DIR__ . '/../src/PivotTable.php');
require (__DIR__ . '/../src/Database.php');
require (__DIR__ . '/../src/Util.php');


function getConnection() {
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
    return $con;
}




function monthlySales($con) {
    $pivot_column_values_sql  = 'SELECT DISTINCT DATE_FORMAT(order_date, "%Y-%m") AS sale_date ';
    $pivot_column_values_sql .= 'FROM sale_item ';
    $pivot_column_values_sql .= 'WHERE order_date BETWEEN "2014-01-01" AND "2014-12-31" ';
    $pivot_column_values_sql .= 'ORDER BY sale_date';

    $data_sql  = 'SELECT category, DATE_FORMAT(order_date, "%Y-%m") AS sale_date, ';
    $data_sql .= '       SUM(amt) AS sum_amt, SUM(sales) AS sum_sales, SUM(products) AS sum_products ';
    $data_sql .= 'FROM   sale_item ';
    $data_sql .= 'WHERE  order_date BETWEEN "2014-01-01" AND "2014-12-31" ';
    $data_sql .= 'GROUP BY category, sale_date ';
    $data_sql .= 'ORDER BY category, sale_date ';

    try {
        $pivot_column_values = $con->select($pivot_column_values_sql, array('sale_date'));
        $results_data = $con->select($data_sql, array('category', 'sale_date', 'sum_amt', 'sum_sales', 'sum_products'));
    } catch (Exception $e) {
        print $e->geteMessage();
        return;
    }


    $pivot_row = array('category' => 'Product Category');
    $pivot_column = array('sale_date' => 'Sale Month');

    $decorator = array(
        'table' => 'table table-condensed table-striped table-bordered table-text-center',
        'total_row' => 'info text-right',
        'pivot_row' => 'success text-left',
        'column' => 'text-right'
    );

    $pt = new PivotTable(array('heading_all' => 'Grand Total'));


    $summation_columns = array('sum_amt' => 'Sales $');
    $a = $pt->summarize($results_data, $pivot_column_values, $pivot_row, $pivot_column, $summation_columns);
    print '<h2>Product Type by Month Example (Sales) <small>'.Util::downloadLink('demo1', $a).'</small></h2>';
    $o = $pt->render($a, $decorator, count($summation_columns));
    print $o;

    $summation_columns = array('sum_amt' => 'Sales $','sum_products' => 'Products');
    $a = $pt->summarize($results_data, $pivot_column_values, $pivot_row, $pivot_column, $summation_columns);
    print '<h2>Product Type by Month Example (Sales,Products) <small>'.Util::downloadLink('demo2', $a).'</small></h2>';
    $o = $pt->render($a, $decorator, count($summation_columns));
    print $o;
}




include ('header.php');
$con = getConnection();
monthlySales($con);
$con->close();
include ('footer.php');
