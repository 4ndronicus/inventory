<?php

class CSVObj extends classBase {

    public function __construct() {
        parent::__construct();
        parent::log(2, "Entering " . __FUNCTION__ . " of class " . __CLASS__);
    }

    public function queryToCSVDownload($header, $query) {
        parent::log(2, "Entering " . __FUNCTION__ . " of class " . __CLASS__);
        global $XWV;

        require_once( $XWV['inc'] . "/dbconn.class.php" );
        parent::log(2, "Executing query");
        $db = new dbConn();
        $ret = $db->doSel($query);

        if (isblank($ret) || $db->numrows <= 0) {
            parent::log(0, "In function " . __FUNCTION__ . " of class " . __CLASS__ . " query failed: $query");
        }

        parent::log(2, "Opening 'output' for writing");
        $tmpfile = fopen('php://output', 'w');

        if ($tmpfile && $ret && $header) {
            parent::log(2, "Sending headers for csv download");
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="report.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            $rowct = count($ret);
            parent::log(2, "Writing out $rowct lines to csv data");
            fputcsv($tmpfile, $header);
            for ($i = 0; $i < $rowct; $i++) {
                fputcsv($tmpfile, $ret[$i]);
            }
            parent::log(2, "Finished writing out csv data");
        }
        die;
    }

}
