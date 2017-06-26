<?php

session_start();
require_once( dirname( __FILE__ ) . "/config.inc" );
//require_once( "header.php" );

//show_globals();

$vars['msg'] = ""; // Initialize a message placeholder
$ipaddr = $_GET['h'];
$vars['back_link'] = $_SESSION['LAST_RANGE'];

/*
 * Process any data that was posted back to this script
 */
if (!isblank($_POST)) {

    $decom = "false";
    if ($_POST['decom'] == "Yes") {
        $decom = "true";
    }

    $inuse = "false";
    if ($_POST['inuse'] == "Yes") {
        $inuse = "true";
    }
    
    $pm = "false";
    if ( $_POST['pm'] == "Yes"){
        $pm = "true";
    }
    
    $patchdate = "0000-00-00";
    if( $_POST['patched'] == "on"){
        $patchdate = date("Y-m-d");
    }
    
    /*
     * Update the database with the new values
     */
    $query = "update hosts set "
            . "hostname='" . $_POST['hostname'] . "', "
            . "netbiosdomain='" . $_POST['netbiosdomain'] . "', "
            . "netbiosname='" . $_POST['netbiosname'] . "', "
            . "location='" . $_POST['location'] . "', "
            . "envtype='" . $_POST['envtype'] . "', "
            . "company='" . $_POST['company'] . "', "
            . "owner='" . $_POST['owner'] . "', "
            . "product='" . $_POST['product'] . "', "
            . "poc='" . $_POST['poc'] . "', "
            . "bu='" . $_POST['bu'] . "', "
            . "purpose='" . $_POST['purpose'] . "', "
            . "os='" . $_POST['os'] . "', "
            . "decom=" . $decom . ", "
            . "inuse=" . $inuse . " "
            . "where ipaddr='" . $ipaddr . "'";

    if ($db->doExec($query) === true) {
        $vars['msg'] = "Updated host: " . $ipaddr;// . "query : " . $query;
    } else {
        $vars['msg'] = "Failed to update host: " . $ipaddr;
    }
    unset($query);
}

/*
 * Pull the host data from the database
 */
$query = "select * from hosts where ipaddr='$ipaddr'";

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
$vars['poc'] = $rec['poc'];
$vars['bu'] = $rec['bu'];
$vars['purpose'] = $rec['purpose'];
$vars['os'] = $rec['os'];
$vars['decom'] = $rec['decom'];
$vars['inuse'] = $rec['inuse'];

// which host are we looking at?
$vars['action'] = $_SERVER['PHP_SELF'] . "?h=" . $ipaddr;


/*
 *  build drop-down selection for netbiosdomain
 */
$query = "select distinct(netbiosdomain) from hosts order by netbiosdomain asc";
$netbiosdomain = $db->doSel($query);
if ($netbiosdomain === false && $db->numrows < 0) {
    showMsg("Unknown error retrieving netbiosdomain data");
}
foreach ($netbiosdomain as $key => $val) {
    $selarr[] = $val['netbiosdomain'];
}
$vars['sel_netbiosdomain'] = createSelect("netbiosdomain", $rec['netbiosdomain'], $selarr);
unset($selarr);


/*
 *  build drop-down selection for location
 */
$query = "select distinct(location) from hosts order by location asc";
$loc = $db->doSel($query);
if ($loc === false && $db->numrows < 0) {
    showMsg("Unknown error retrieving location data");
}
foreach ($loc as $key => $val) {
    $selarr[] = $val['location'];
}
$vars['sel_loc'] = createSelect("location", $rec['location'], $selarr);
unset($selarr);


/*
 *  build drop-down selection for environment
 */
$query = "select distinct(envtype) from hosts order by envtype asc";
$envtype = $db->doSel($query);
if ($envtype === false && $db->numrows < 0) {
    showMsg("Unknown error retrieving environment data");
}
foreach ($envtype as $key => $val) {
    $selarr[] = $val['envtype'];
}
$vars['sel_envtype'] = createSelect("envtype", $rec['envtype'], $selarr);
unset($selarr);


/*
 *  build drop-down selection for the company that owns this host
 */
$query = "select distinct(company) from hosts order by company asc";
$company = $db->doSel($query);
if ($company === false && $db->numrows < 0) {
    showMsg("Unknown error retrieving company data");
}
foreach ($company as $key => $val) {
    $selarr[] = $val['company'];
}
$vars['sel_company'] = createSelect("company", $rec['company'], $selarr);
unset($selarr);


/*
 *  build drop-down selection for who owns this host
 */
$query = "select distinct(owner) from hosts order by owner asc";
$owner = $db->doSel($query);
if ($owner === false && $db->numrows < 0) {
    showMsg("Unknown error retrieving owner data");
}
foreach ($owner as $key => $val) {
    $selarr[] = $val['owner'];
}
$vars['sel_owner'] = createSelect("owner", $rec['owner'], $selarr);
unset($selarr);


/*
 *  build drop-down selection for the product that this host is used for
 */
$query = "select distinct(product) from hosts order by product asc";
$product = $db->doSel($query);
if ($product === false && $db->numrows < 0) {
    showMsg("Unknown error retrieving product data");
}
foreach ($product as $key => $val) {
    $selarr[] = $val['product'];
}
$vars['sel_product'] = createSelect("product", $rec['product'], $selarr);
unset($selarr);


/*
 *  build drop-down selection for the point of contact for this host
 */
$query = "select distinct(poc) from hosts order by poc asc";
$poc = $db->doSel($query);
if ($poc === false && $db->numrows < 0) {
    showMsg("Unknown error retrieving contact data");
}
foreach ($poc as $key => $val) {
    $selarr[] = $val['poc'];
}
$vars['sel_poc'] = createSelect("poc", $rec['poc'], $selarr);
unset($selarr);


/*
 *  build drop-down selection for the business unit this host is used for
 */
$query = "select distinct(bu) from hosts order by bu asc";
$bu = $db->doSel($query);
if ($bu === false && $db->numrows < 0) {
    showMsg("Unknown error retrieving BU data");
}
foreach ($bu as $key => $val) {
    $selarr[] = $val['bu'];
}
$vars['sel_bu'] = createSelect("bu", $rec['bu'], $selarr);
unset($selarr);

/*
 *  build drop-down selection for the purpose of this host
 */
$query = "select distinct(purpose) from hosts order by purpose asc";
$purpose = $db->doSel($query);
if ($purpose === false && $db->numrows < 0) {
    showMsg("Unknown error retrieving purpose data");
}
foreach ($purpose as $key => $val) {
    $selarr[] = $val['purpose'];
}
$vars['sel_purpose'] = createSelect("purpose", $rec['purpose'], $selarr);
unset($selarr);


/*
 *  build drop-down selection for the OS that is on this host
 */
$query = "select distinct(os) from hosts order by os asc";
$os = $db->doSel($query);
if ($os === false && $db->numrows < 0) {
    showMsg("Unknown error retrieving OS data");
}
foreach ($os as $key => $val) {
    $selarr[] = $val['os'];
}
$vars['sel_os'] = createSelect("os", $rec['os'], $selarr);
unset($selarr);


// create array for simple yes/no drop-down select object
$selarr[] = "Yes";
$selarr[] = "No";

/*
 * Select drop-down for whether a system will be or has been decommissioned
 */
$selected = "No";
if ($rec['decom'] == true) {
    $selected = "Yes";
}
$vars['sel_decom'] = createSelect("decom", $selected, $selarr);

/*
 *  select drop-down to set flag for whether this server is in use
 */
$selected = "No";
if ($rec['inuse'] == true) {
    $selected = "Yes";
}
$vars['sel_inuse'] = createSelect("inuse", $selected, $selarr);

/*
 * Run the variables through the templating system and display the page
 */
$HTML = replace($vars, rf($XWV['tpl'] . "/edit.html"));
echo $HTML;

?>