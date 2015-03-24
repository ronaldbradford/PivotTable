<?php

namespace PivotTable;

class PivotTable
{

    public function __construct()
    {
    }

    public function summarize(
        $con,
        $columns_stmt,
        $data_stmt,
        $pivot_column,
        $name_column,
        $cnt_columns,
        $pivot_column_heading
    ) {

        $data = array();
        if ($columns_stmt) {
            $rs = $this->select($con, $columns_stmt);
            if ($rs) {
                $pre_columns = array();
                $pre_column_totals = array();
                while ($row = mysqli_fetch_row($rs)) {
                    $pre_columns[$row[0]] = '';
                    $pre_column_totals[$row[0]] = 0;
                }
                free($rs);
            } else {
                return $data;
            } // if $rs
        } else {
            return $data;
        } // if $columns_stmt

        $cnt_column = explode(',', $cnt_columns);
        $columns=array();
        $column_totals = array();
        for ($i=0; $i < count($cnt_column); $i++) {
            $columns[] = $pre_columns;
            $column_totals[] = $pre_column_totals;
        }

        $row_data = array();
        $row_data[] = $pivot_column_heading;
        $row_column_data = array();
        $row_column_data[] = '';
        foreach ($pre_columns as $c => $n) {
            for ($i=0; $i < count($cnt_column); $i++) {
                $row_data[] = $c;
                $row_column_data[] = $cnt_column[$i];
            }
        }
        $row_data[] = 'Total';
        $row_column_data[] = '';
        $data[] = $row_data;
        if (count($cnt_column) > 1) {
            $data[] = $row_column_data;
        }

        if ($data_stmt) {
            $rs = select($con, $data_stmt);

            if ($rs && $rs instanceof mysqli_result) {
                // Per row variables
                $row_pivot='';
                $row_total=0;
                $row_columns = $columns;

                while ($r = mysqli_fetch_assoc($rs)) {
                    $current_pivot = $r[$pivot_column];
                    if ($row_pivot != $current_pivot) {   // A change in the pivot column
                        if ($row_pivot != '') { // i.e. not first time in this loop
                            $row_data = array();
                            $row_data[] = $row_pivot;

                            foreach (array_keys($row_columns[0]) as $k) {
                                for ($i=0; $i < count($cnt_column); $i++) {
                                    $row_data[] = $row_columns[$i][$k];
                                }
                            }
                            $row_data[] = $row_total;
                            $data[] = $row_data;
                        } // $row_pivot != ''
                        $row_pivot = $current_pivot;
                        $row_total=0;
                        $row_columns = $columns;
                    } // != $current_pivot
                    for ($i=0; $i < count($cnt_column); $i++) {
                        $row_columns[$i][$r[$name_column]] = $r[$cnt_column[$i]];
                        $column_totals[$i][$r[$name_column]] += $r[$cnt_column[$i]];
                    }
                    $row_total += $r[$cnt_column[0]];
                } // while
                free($rs);

                if (isset($current_pivot)) {
                    $row_data = array();
                    $row_data[] = $row_pivot;

                    foreach (array_keys($row_columns[0]) as $k) {
                        for ($i=0; $i < count($cnt_column); $i++) {
                            $row_data[] = $row_columns[$i][$k];
                        }
                    }
                    $row_data[] = $row_total;
                    $data[] = $row_data;
                } // Cater for empty result set
            } // if $rs
        } // $data_stmt



        $row_data = array();
        $row_data[] = 'All';
        $row_total=0;

        foreach (array_keys($column_totals[0]) as $k) {
            for ($i=0; $i < count($cnt_column); $i++) {
                $row_data[] = $column_totals[$i][$k];
            }
            $row_total += $n;
        }

        $row_data[] = $row_total;
        $data[] = $row_data;

        return $data;
    }

    private function select(
        $con,
        $sql
    ) {
        if (empty($sql)) {
            return;
        }
        if (empty($con)) {
            return;
        }
        if (gettype($sql) == 'string') {
            $rs = $con->query($sql);
        } elseif ($sql instanceof mysqli_stmt) {
            $sql->execute();
            $rs = $sql->get_result();
        } else {
            return;
        }
        if (!$rs) {
            return;
        }
        return $rs;
    }

    public function render($data)
    {
        $output = '<table>';
        $heading=true;
        $extra='';
        foreach ($data as $r) {
            if ($heading) {
                $headerline=$r;
            }
            if ($r[0] == 'All') {
                $extra=' class="info"';
                $heading=true;
            }
            $output .= '<tr'.$extra.'>';
            $extra='';
            $l = count($r);
            $p = 0;
            foreach ($r as $n => $v) {
                $el = $heading ? 'h' : 'd';
                $p++;
                if ($p == 1) {
                    $extra=' class="aleft"';
                    $el='h';
                } // First Column
                if ($p == $l) {
                    $extra=' class="info"';
                    $el='h';
                } // Last Row
                $output .= '<t'.$el.$extra.'>'.$v.'</t'.$el.'>';
                $extra='';
                $el='d';
            }
            $heading=false;
            $output .= '</tr>';
        } // foreach

        if (isset($headerline) && count($data) > 15) {
            $output .= '<tr>';
            foreach ($headerline as $n => $v) {
                $el = 'h';
                $output .= '<t'.$el.$extra.'>'.$v.'</t'.$el.'>';
            }
            $output .= '</tr>';
        }
        $output .= '</table>';

        return $output;
    }
}
