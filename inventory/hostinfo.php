<?php

session_start();
require_once( dirname( __FILE__ ) . "/config.inc" );

$vars[msg] = "";
$ipaddr = $_GET['h'];
$vars[back_link] = $_SESSION['LAST_RANGE'];

$query = "select h.* "
        . "from hosts h "
        . "where h.ipaddr='$ipaddr'";

$ret = $db->doSel($query);

if ($ret === false && $db->numrows < 0) {
    showMsg("Unknown error retrieving host data");
}

$rec = $ret[0]; // pull data from db results
//$vars['hostid'] = $rec['hostid'];
$vars['ipaddr'] = $rec['ipaddr'];
$vars['hostname'] = $rec['hostname'];
$vars['netbiosdomain'] = $rec['netbiosdomain'];
$vars['netbiosname'] = $rec['netbiosname'];
$vars['location'] = $rec['location'];
$vars['envtype'] = $rec['envtype'];
$vars['company'] = $rec['company'];
$vars['owner'] = $rec['owner'];
$vars['product'] = $rec['product'];
$vars['bu'] = $rec['bu'];
$vars['purpose'] = $rec['purpose'];
$vars['os'] = $rec['os'];
$vars['decom'] = "No";
if ($rec['decom'] == 1) {
    $vars['decom'] = "Yes";
}
$vars['inuse'] = "No";
if ($rec['inuse'] == 1) {
    $vars['inuse'] = "Yes";
}
$vars['macaddr'] = $rec['macaddr'];
$vars['manuf'] = $rec['manuf'];
$vars['model'] = $rec['model'];
$vars['connfrom'] = $rec['connfrom'];

unset($ret);

$HTML = replace($vars, rf($XWV['tpl'] . "/hostinfo.html"));

echo $HTML;
?>