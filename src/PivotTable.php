<?php

namespace PivotTable;

class PivotTable
{

    public function __construct()
    {
    }

    public function summarize(
        $con,
        $pivot_column_values_stmt,
        $data_stmt,
        $pivot_row,
        $pivot_column,
        $summation_columns
    ) {

        $data = array();
        if ($pivot_column_values_stmt) {
            $rs = $this->select($con, $pivot_column_values_stmt);
            if ($rs) {
                $initialized_columns_values = array();
                $initialized_columns_totals = array();
                while ($row = mysqli_fetch_row($rs)) {
                    $initialized_columns_values[$row[0]] = '';
                    $initialized_columns_totals[$row[0]] = 0;
                }
                if ($rs) {
                    \mysqli_free_result($rs);
                }
            } else {
                throw new \Exception('No results for columns');
            } // if $rs
        } else {
            throw new \Exception('No columns statement');
        } // if $pivot_column_values_stmt

        $initialized_summation_columns=array();
        $initialized_summation_column_totals=array();
        $summation_columns_totals = array();

        $cnt_column = array_keys($summation_columns);
        for ($i=0; $i < count(array_keys($summation_columns)); $i++) {
            $initialized_summation_columns[] = $initialized_columns_values;
            $initialized_summation_column_totals[] = 0;
            $summation_columns_totals[] = $initialized_columns_totals;
        }

        // Create Initial Header Rows
        // First Row is the pivot column values names
        // Second Row is the summation columns per pivot column value
        $row_headings_data = array();
        $row_headings_summmation_names = array();
        foreach (array_values($pivot_row) as $pivot_row_heading) {
            $row_headings_data[] = $pivot_row_heading;
            $row_headings_summation_names[] = '';
        }
        foreach ($initialized_columns_values as $c => $n) {
            for ($i=0; $i < count(array_keys($summation_columns)); $i++) {
                $row_headings_data[] = $c;
                $row_headings_summation_names[] = $summation_columns[$cnt_column[$i]];
            }
        }
        for ($i=0; $i < count(array_keys($summation_columns)); $i++) {
            $row_headings_data[] = 'Total';
            $row_headings_summation_names[] = $summation_columns[$cnt_column[$i]];
        }
        $data[] = $row_headings_data;
        $data[] = $row_headings_summation_names;

        $pivot_column_name=array_keys($pivot_column)[0];
        if ($data_stmt) {
            $rs = $this->select($con, $data_stmt);

            if ($rs && $rs instanceof \mysqli_result) {
                // Per row variables
                $row_pivot   = '';
                $row_columns = $initialized_summation_columns;
                $row_totals  = $initialized_summation_column_totals;

                while ($r = \mysqli_fetch_assoc($rs)) {
                    $current_pivot='';
                    foreach (array_keys($pivot_row) as $pivot_row_column) {
                        $current_pivot .= $r[$pivot_row_column] . "\t";
                    }
                    if ($row_pivot != $current_pivot) {   // A change in the pivot column
                        if ($row_pivot != '') { // i.e. not first time in this loop
                            $row_data = array();
                            $row_data[] = $row_pivot;

                            foreach (array_keys($row_columns[0]) as $k) {
                                for ($i=0; $i < count(array_keys($summation_columns)); $i++) {
                                    $row_data[] = $row_columns[$i][$k];
                                }
                            }
                            for ($i=0; $i < count(array_keys($row_totals)); $i++) {
                                $row_data[] = $row_totals[$i];
                            }
                            $data[] = $row_data;
                        } // $row_pivot != ''
                        $row_pivot   = $current_pivot;
                        $row_columns = $initialized_summation_columns;
                        $row_totals  = $initialized_summation_column_totals;
                    } // != $current_pivot
                    for ($i=0; $i < count(array_keys($summation_columns)); $i++) {
                        $row_columns[$i][$r[$pivot_column_name]] = $r[$cnt_column[$i]];
                        $row_totals[$i] += $r[$cnt_column[$i]];
                        $summation_columns_totals[$i][$r[$pivot_column_name]] += $r[$cnt_column[$i]];
                    }
                } // while
                if ($rs) {
                    \mysqli_free_result($rs);
                }

                if (isset($current_pivot)) {
                    $row_data = array();
                    $row_data[] = $row_pivot;

                    foreach (array_keys($row_columns[0]) as $k) {
                        for ($i=0; $i < count(array_keys($summation_columns)); $i++) {
                            $row_data[] = $row_columns[$i][$k];
                        }
                    }
                    for ($i=0; $i < count(array_keys($row_totals)); $i++) {
                        $row_data[] = $row_totals[$i];
                    }
                    $data[] = $row_data;
                } // Cater for empty result set
            } // if $rs
        } // $data_stmt

        $row_data = array();
        $row_data[] = 'All';
        $row_totals = $initialized_summation_column_totals;

        foreach (array_keys($summation_columns_totals[0]) as $k) {
            for ($i=0; $i < count(array_keys($summation_columns)); $i++) {
                $row_data[] = $summation_columns_totals[$i][$k];
                $row_totals[$i] += $summation_columns_totals[$i][$k];
            }
        }
        for ($i=0; $i < count(array_keys($row_totals)); $i++) {
            $row_data[] = $row_totals[$i];
        }
        $data[] = $row_data;

        return $data;
    }

    public function render($data, $decorator, $summation_column_count)
    {
        $table_class = (isset($decorator['table']) ? $decorator['table'] : '');
        $pivot_row_class = (isset($decorator['pivot_row']) ? $decorator['pivot_row'] : 'aleft');
        $total_row_class = (isset($decorator['total_row']) ? $decorator['pivot_row'] : 'info');
        $output = '<table class="'. $table_class .'">';
        $first_heading_row = true;
        $extra = '';
        foreach ($data as $r) {
            //  Keep a copy of the first heading line for later
            if ($first_heading_row) {
                $first_heading_row = false;
                $heading = true;
                $headerline = $r;
            }
            if ($r[0] == 'All') {
                $extra = ' class="'.$total_row_class.'"';
                $heading = true;
            }
            $output .= '<tr'.$extra.'>';


            $extra = '';
            $l = count($r) - $summation_column_count;
            $p = 0;
            foreach ($r as $n => $v) {
                $el = $heading ? 'h' : 'd';
                $p++;
                // Change element and select style for rirst column of row information
                if ($p == 1) {
                    $extra = ' class="'.$pivot_row_class.'"';
                    $el = 'h';
                } // First Column
                // Change element and select style for class column of row information
                if ($p > $l) {
                    $extra = ' class="'. $total_row_class.'"';
                    $el = 'h';
                } // Last Row
                $output .= '<t'.$el.$extra.'>'.$v.'</t'.$el.'>';
                $extra = '';
                $el = 'd';
            }
            $heading = false;
            $output .= '</tr>';
        } // foreach

        // If there are more than 15 lines of output, repeat the heading line at bottom of table
        if (isset($headerline) && count($data) > 15) {
            $output .= '<tr>';
            $el = 'h';
            foreach ($headerline as $n => $v) {
                $output .= '<t'.$el.$extra.'>'.$v.'</t'.$el.'>';
            }
            $output .= '</tr>';
        }
        $output .= '</table>';

        return $output;
    }
}
