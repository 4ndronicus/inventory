<?php

class classShowTable extends classBase {

    public function __construct() {
        parent::__construct();
        parent::log(2, "Entering " . __FUNCTION__ . " of class " . __CLASS__);
    }

    public function buildTable($header_array, $query) {
        parent::log(2, "Entering " . __FUNCTION__ . " of class " . __CLASS__);
        global $XWV;
        require_once( $XWV['inc'] . "/dbconn.class.php" );

        $db = new dbConn();

//        parent::log(2, "Attempted query: $query");
        $tblCts = $db->doSel($query);

        if (empty($tblCts) || !isset($tblCts) || $db->numrows <= 0) {

            parent::log(0, "In function " . __FUNCTION__ . " of class " . __CLASS__ . " query failed: $query", "err");
            $vars['msg'] = "No results found.";
            return replace($vars, rf($XWV['tpl'] . "/error.html"));
        }

        $row = $tblCts[0];
        $colct = count($row);
        $rowct = count($tblCts);
        
        parent::log(2, "Constructing table for report.\n");

        $html = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"3\">\n";
        $html.= "<tr>\n";
        foreach( $header_array as $idx => $header ){
            $html.= "<th nowrap>\n";
            $html.= $header . "\n";
            $html.= "</th>\n";
        }
        $html.= "</tr>\n";

        for ($n = 0; $n < $rowct; $n++) {
            $html.="<tr>\n";
            foreach( $tblCts[$n] as $idx => $field ){
                $html.="<td>\n";
                $html.= $field . "\n";
                $html.="</td>\n";
            }
            $html.="</tr>\n";
        }
        parent::log(2, "Report created.\n" );
        
        parent::log(2, "Exiting " . __FUNCTION__ . " of class " . __CLASS__);

        return $html;
    }

}
