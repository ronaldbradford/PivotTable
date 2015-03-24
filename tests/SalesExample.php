<?php
namespace PivotTable;

require (__DIR__ . '/../src/PivotTable.php');

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
            $con = new \mysqli($host, $user, $password, $database);
            if ($con->connect_errno) {
                print 'Unable to obtain a database connection. '.mysqli_connect_error();
                return;
            }
        } catch (Exception $e) {
            print 'Exception: Unable to obtain a database connection ' .$e->geteMessage();
            return;
        }


        $columns_stmt = $con->prepare('SELECT DISTINCT sale_date FROM sale ORDER BY sale_date');
        $data_stmt = $con->prepare('SELECT product_type, sale_date, SUM(amt) AS sum_amt, SUM(qty) AS sum_qty FROM   sale GROUP BY product_type, sale_date ORDER BY product_type, sale_date');
        $a = $pt->summarize($con, $columns_stmt, $data_stmt, 'product_type', 'sales_date', 'sum_amt', 'Product Type');

        $o = $pt->render($a);

        if ($con) mysqli_close($con);

        print $o;
    }
}

$example = new SalesExample();
