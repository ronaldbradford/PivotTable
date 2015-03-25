<?php

namespace PivotTable;

class Database
{
    private $con = false;

    private function __construct()
    {
    } // __construct

    public static function getConnection($host, $user, $passwd, $database, $port = '', $socket = '')
    {
        $db = new Database();
        try {
            $db->con = new \mysqli($host, $user, $passwd, $database); // $port, $socket
            if ($db->con->connect_errno) {
                throw new Exception('Unable to obtain a database connection. '.mysqli_connect_error());
            }
        } catch (Exception $e) {
            throw new Exception('Exception: Unable to obtain a database connection ' .$e->geteMessage());
        }

       
        return $db;
    }

    public function close()
    {
        if ($this->con && $this->con instanceof mysqli_connection) {
             mysqli_close($this->con);
             $this->con = false;
        }
    }

    public function prepare($sql)
    {
        return $this->con->prepare($sql);
    }

    public function select($sql, $columns)
    {
        if (!$this->con) {
            return;
        }
        if (empty($sql)) {
            return;
        }
        if (empty($columns) || !is_array($columns) || count($columns) == 0) {
            return;
        }
        if (gettype($sql) == 'string') {
            $result_set = $this->con->query($sql);
        } elseif ($sql instanceof \mysqli_stmt) {
            $sql->execute();
            $result_set = $sql->get_result();
        } else {
            return;
        }
        if (!$result_set) {
            return;
        }

        return $this->response($result_set, $columns);
    }

    private function responseRow($result_set_row, $columns)
    {
        $row = array();
        foreach ($columns as $column_name) {
            $row[$column_name] = $result_set_row[$column_name];
        }
        return $row;
    }

    private function response($result_set, $columns)
    {
        $results = array();
        if ($result_set && $result_set instanceof \mysqli_result) {
            while ($row = mysqli_fetch_assoc($result_set)) {
                $results[] = $this->responseRow($row, $columns);
            }
        }
        $this->free($result_set);
        return $results;
    }

    private function free($result_set)
    {
        if ($result_set instanceof mysqli_result) {
            mysqli_free_result($result_set);
        }
    }
}
