<?php

namespace PivotTable;

class Util
{

    const TAB = "\t";
    const NEWLINE = "\n";

    public function __construct()
    {
    } // __construct

    public static function a2TSV($data)
    {
        $output = '';
        foreach ($data as $r) {
            foreach ($r as $n => $v) {
                $output .= $v.Util::TAB;
            }
            $output .= Util::NEWLINE;
        }  // foreach row
        return $output;
    } // a2tsv


    public static function downloadLink($filetype, $data)
    {
        $dt = date('Y-m-d');
        $filename = $dt.'-'.$filetype.'.tsv';
        $tsv = Util::a2TSV($data);
        return '<a class="btn btn-warning" ' .
           'href="data:application/octet-stream;charset=utf-16le;base64,'.base64_encode($tsv).'" '.
           'download="'.$filename.'">Download</a>';
    } // downloadLink
}
