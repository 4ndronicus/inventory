<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

session_start();
require_once( dirname( __FILE__ ) . "/config.inc" );
require_once( "header.php" );

debuglog( "Loading page: " . __FILE__ );

$vars['msg'] = "";

$sort = $_GET['sort'];

$how = $_GET['h'];

$query = "select ipaddr, hostname, location, envtype, owner, product, "
        . "poc, purpose, os, macaddr, manuf, last_seen  from hosts h "
        . "where ";

$query .= "h.purpose !=\"Workstation\" and h.inuse = true";

if ( isblank( $sort ) ) {
    $sort = "ipaddr";
}

/*
 * Alternate sort methods betwen ascending and descending
 */
if ( isblank( $how ) ) {
    $how = "asc";
    $nexthow = "asc";
} else {
    if ( $how == "asc" ) {
        $nexthow = "desc";
    } else {
        $nexthow = "asc";
    }
}

$query .= " order by " . $sort . " " . $how;

debuglog( $query );

/*
 * Do the query
 */
$ret = $db->doSel( $query );

/*
 * Check our results
 */
if ( $ret === false ) {

    if ( $db->numrows == 0 ) {
        showMsg( "No records found." );
    } else {
        showMsg( "Unable to pull data." );
    }
}

/*
 * Begin populating the template data
 */
$rowct = $db->numrows;

$vars['range_table'] = "";

$header['ipaddr'] = "IP Address";
$header['hostname'] = "Hostname";
$header['location'] = "Location";
$header['envtype'] = "Environment";
$header['owner'] = "Owner";
$header['product'] = "Product";
$header['poc'] = "Contact";
$header['purpose'] = "Purpose";
$header['os'] = "OS";
$header['macaddr'] = "MAC Address";
$header['manuf'] = "NIC Manufacturer";
$header['last_seen'] = "Seen";

// SET UP THE DOWNLOAD OF THIS REPORT
if ( $_GET['dl'] === 'y' && !isblank( $_GET['dl'] ) ) {
    foreach ( $header as $key => $name ) {
        $header_array[] = $name;
    }
    $csvObj = new CSVObj();
    $csvObj->queryToCSVDownload( $header_array, $query );
}

if ( strpos( $_SERVER['REQUEST_URI'], "?" ) !== false ) {
    $vars['csv_download'] = $_SERVER['REQUEST_URI'] . "&dl=y";
} else {
    $vars['csv_download'] = $_SERVER['REQUEST_URI'] . "?dl=y";
}

// used for getting back to this page from the edit page
$_SESSION['LAST_RANGE'] = $_SERVER['REQUEST_URI'];


$header['sort_by_ipaddr_link'] = $_SERVER['PHP_SELF'] . "?sort=ipaddr&h=" . $nexthow;
$header['sort_by_hostname_link'] = $_SERVER['PHP_SELF'] . "?sort=hostname&h=" . $nexthow;
$header['sort_by_location_link'] = $_SERVER['PHP_SELF'] . "?sort=location&h=" . $nexthow;
$header['sort_by_envtype_link'] = $_SERVER['PHP_SELF'] . "?sort=envtype&h=" . $nexthow;
$header['sort_by_owner_link'] = $_SERVER['PHP_SELF'] . "?sort=owner&h=" . $nexthow;
$header['sort_by_product_link'] = $_SERVER['PHP_SELF'] . "?sort=product&h=" . $nexthow;
$header['sort_by_poc_link'] = $_SERVER['PHP_SELF'] . "?sort=poc&h=" . $nexthow;
$header['sort_by_purpose_link'] = $_SERVER['PHP_SELF'] . "?sort=purpose&h=" . $nexthow;
$header['sort_by_os_link'] = $_SERVER['PHP_SELF'] . "?sort=os&h=" . $nexthow;
$header['sort_by_macaddr_link'] = $_SERVER['PHP_SELF'] . "?sort=macaddr&h=" . $nexthow;
$header['sort_by_manuf_link'] = $_SERVER['PHP_SELF'] . "?sort=manuf&h=" . $nexthow;
$header['sort_by_last_seen_link'] = $_SERVER['PHP_SELF'] . "?sort=last_seen&h=" . $nexthow;

/*
 * Render the header of the table
 */
$vars['range_table'] .= replace( $header, rf( $XWV['tpl'] . "/index_header_row.html" ) );

/*
 * Render the rest of the rows of the table
 */
for ( $i = 0; $i < $rowct; $i++ ) {

    unset( $rowvars );

    $rowvars['tr_class'] = "even";
    if ( $i % 2 == 1 ) {
        $rowvars['tr_class'] = "odd";
    }

    $rowvars['ipaddr'] = $ret[$i]['ipaddr'];
    $rowvars['hostname'] = $ret[$i]['hostname'];
    $rowvars['location'] = $ret[$i]['location'];
    $rowvars['last_seen'] = $ret[$i]['last_seen'];
    $rowvars['envtype'] = $ret[$i]['envtype'];
    $rowvars['owner'] = $ret[$i]['owner'];
    $rowvars['product'] = $ret[$i]['product'];
    $rowvars['poc'] = $ret[$i]['poc'];
    $rowvars['purpose'] = $ret[$i]['purpose'];
    $rowvars['os'] = $ret[$i]['os'];
    $rowvars['manuf'] = $ret[$i]['manuf'];
    $rowvars['macaddr'] = $ret[$i]['macaddr'];

    $vars['range_table'] .= replace( $rowvars, rf( $XWV['tpl'] . "/index_row.html" ) );
}

/*
 * Render the entire page
 */
$HTML = replace( $vars, rf( $XWV['tpl'] . "/index.html" ) );

/*
 * Display the page
 */
echo $HTML;

debuglog( "End of page load: " . __FILE__ );
