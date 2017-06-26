<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require_once( dirname( __FILE__ ) . "/config.inc" );

$out = var_export( $_REQUEST, true );
debuglog( "\n\nREQUEST:\n" . $out );

/*
 * Figure out the hostname
 */
debuglog( "Hostname: $hostname" );
$hostname = strtolower( $_REQUEST['hostname'] );
if ( isblank( $hostname ) ) {
    $hostname = strtolower( $_REQUEST['dnshost'] );
}
if ( isblank( $hostname ) ) {
    $hostname = strtolower( $_REQUEST['arphost'] );
}
$host_arr = explode( ".", $hostname );
$hostname = $host_arr[0];
$hostname = trim( str_replace( "?", "", $hostname ) );

debuglog( "Hostname: $hostname" );

/*
 * Figure out the IP address
 */
$ipaddr = $_REQUEST['ipaddr'];

$ip_arr = explode( ".", $ipaddr );
$class_c = $ip_arr[0] . "." . $ip_arr[1] . "." . $ip_arr[2];

/*
 * Figure out the location and environment if applicable
 */
switch ( $class_c ) {
    case "172.23.200":
        $envtype = "Mgmt";
    case "172.23.201":
    case "172.23.202":
        $location = "China";
        break;
    case "10.50.20":
        $envtype = "Mgmt";
    case "10.50.18":
    case "10.50.19":
        $location = "New York";
        break;
}

/*
 * Determine whether it's in use
 */
$inuse = 0;
if ( $_REQUEST['alive'] === "UP" ) {
    $inuse = 1;
}

/*
 * Get the Operating System
 */
$os = addslashes( urldecode( $_REQUEST['os'] ) );

/*
 * Grab the MAC address
 */
$macaddr = $_REQUEST['mac'];

/*
 * Take a note of the server this information was gathered from
 */
$connfrom = strtolower( $_REQUEST['connfrom'] );
/*
 * Need to add 'architecture' and 'note' to the hosts table
 */

$location = "";
$envtype = "";
$owner = "";
$product = "";
$purpose = "";
$decom = 0;
$pm = 0;
$patched = "0000-00-00";
$poc = "";
$manuf = "";
$model = "";
$last_seen = @date( "Y-m-d" );

/*
 * If our server is not in use, clear it out, mark it as not in use
 */
debuglog( "Is server in use?" );
if ( $inuse === 0 ) {
    debuglog( "No it is not. Disabling and continuing." );
    $query = "update hosts set inuse=0 where ipaddr = '" . $ipaddr . "'";
    debuglog( "Executing query: " . $query );
    $ret = $db->doExec( $query );
    if ( $ret === false ) {
        debuglog( "Unable to update record - query : " . $query );
    } else {
        debuglog( "Record updated successfully..." );
    }
    return;
}
debuglog( "Yes, it is." );

if ( strlen( $hostname > 0 ) ) {
    $where = "where hostname = '" . $hostname . "'";
} else {
    $where = "where ipaddr = '" . $ipaddr . "'";
}

$query = "select * from hosts " . $where;
debuglog( "query: " . $query );

$ret = $db->doSel( $query );

if ( $ret != false && $db->numrows > 0 ) { // if we have a record with the same hostname, gather the data for the new host
    debuglog( $db->numrows . " existing record(s) for hostname " . $hostname . "/ip $ipaddr found. Gathering data for new record." );

    if ( $db->numrows > 1 ) {
        debuglog( "############################ Too many records found for this host. Can only update one! Skipping!" );
    }

    $rec = $ret[0];
    $location = $rec['location'];
    $envtype = $rec['envtype'];
    $owner = $rec['owner'];
    $product = $rec['product'];
    $purpose = $rec['purpose'];
    $decom = $rec['decom'];
    $pm = $rec['pm'];
    $patched = $rec['patched'];
    $poc = $rec['poc'];
    if ( isblank( $os ) && strlen( $rec['os'] ) > 0 ) {
        $os = $rec['os'];
    }
    if ( isblank( $hostname ) && strlen( $rec['hostname'] ) > 0 ) {
        $hostname = $rec['hostname'];
    }
    if ( isblank( $macaddr ) && strlen( $rec['macaddr'] ) > 0 ) {
        $macaddr = $rec['macaddr'];
    }
    if ( isblank( $manuf ) && strlen( $rec['manuf'] ) > 1 ) {
        $manuf = $rec['manuf'];
    }
    if ( isblank( $model ) && strlen( $rec['model'] ) > 1 ) {
        $model = $rec['model'];
    }
    debuglog( "Data retrieved for insertion into new record." );

    $hostid = $rec['hostid'];

    debuglog( "Clearing out old record data for hostid: " . $hostid );

    $query = "update hosts set hostname='', netbiosdomain='', netbiosname='', location='', envtype='', company='', owner='', product='', "
            . "bu='', purpose='', os='', decom=false, inuse=false, pm=false, patched='0000-00-00', poc='', macaddr='', manuf='', model='', "
            . "last_seen='0000-00-00'  where hostid = '" . $hostid . "' limit 1";

    debuglog( "Executing query: " . $query );

    if ( $db->doExec( $query ) === false ) {
        debuglog( "Unable to clear out data for hostid: " . $hostid );
    } else {
        debuglog( "Record data cleared out for hostid: " . $hostid );
    }

// Now have as much data as we can gather about this host from the scan and the database.
// However, if we overwrite another record that has valuable data in it, we will lose that data
// Pull that record before we overwrite it, so that if we find that we need to insert that data again, we have it at least in memory

    debuglog( "Searching for records with IP address of $ipaddr" );

    $query = "select * from hosts where ipaddr = '" . $ipaddr . "'";

    $ret = $db->doSel( $query );

// Do not insert hostnames that are ip addresses
    if ( strpos( $hostname, "10." ) !== false ) {
        $hostname = '';
    }
    if ( strpos( $hostname, "172." ) !== false ) {
        $hostname = '';
    }

    /*
     * See if we can find the NIC manufacturer if we have a MAC address
     */
    if ( strlen( $macaddr ) > 0 ) {
        $mac_arr = explode( ":", $macaddr );
        $mac_chk = $mac_arr[0] . $mac_arr[1] . $mac_arr[2];

        $query = "select manuf from nic_manuf where macpre = '$mac_chk'";
        $mac_ret = $db->doSel( $query );
        if ( $db->numrows > 0 ) {
            debuglog( "Manufacturer: " . $manuf );
            $manuf = $mac_ret[0]['manuf'];
        }
    }
    
    if( strpos( $os, "man-in-the-middle") !== false ){
        $os = "";
    }

// Check to see if a record with that ipaddress exists
    if ( $ret === false && $db->numrows == 0 ) { // if not, do this stuff
        debuglog( "IP address not found... inserting..." );

        $query = "insert into hosts(ipaddr,hostname,netbiosdomain,netbiosname,location,envtype,company,owner,product,bu,purpose,os,decom,inuse,pm,patched,poc,macaddr,manuf,model,last_seen,connfrom) "
                . "values ( '$ipaddr', '" . trim( strtolower($hostname) ) . "', '$netbiosdomain', '$netbiosname', '$location', '$envtype', '$company', '$owner', '$product', '$bu', '$purpose', "
                . "'$os', '$decom', '$inuse', '$pm', '$patched', '$poc', '". strtoupper($macaddr) ."', '$manuf', '$model', '$last_seen', '" . strtolower( $connfrom ) . "')";

        debuglog( "Executing query : " . $query );
        $ret = $db->doExec( $query );
        if ( $ret === false ) {
            debuglog( "Unable to insert record - query : " . $query );
        } else {
            debuglog( "Record updated successfully..." );
        }
    } else {

        // Put the new record in for that IP address
        $query = "update hosts set hostname='" . trim( strtolower($hostname) ) . "', netbiosdomain='$netbiosdomain', location='$location', envtype='$envtype', company='$company', owner='$owner', "
                . "product='$product', bu='$bu', purpose='$purpose', os='$os', decom='$decom', inuse=$inuse, pm='$pm', patched='$patched', poc='$poc', macaddr='". strtoupper($macaddr) ."', "
                . "manuf='$manuf', model='$model', last_seen='$last_seen', connfrom='" . strtolower( $connfrom ) . "' where ipaddr='$ipaddr'";
        debuglog( "Executing query: " . $query );
        $ret = $db->doExec( $query );

        if ( $ret === false ) {
            debuglog( "Unable to update record - query : " . $query );
        } else {
            debuglog( "Record updated successfully..." );
        }
    }
} else {

    debuglog( "Record not found. Inserting." );

// Do not insert hostnames that are ip addresses
    if ( strpos( $hostname, "10." ) !== false ) {
        $hostname = '';
    }
    if ( strpos( $hostname, "172." ) !== false ) {
        $hostname = '';
    }
    /*
     * See if we can find the NIC manufacturer if we have a MAC address
     */
    if ( strlen( $macaddr ) > 0 ) {
        $mac_arr = explode( ":", $macaddr );
        $mac_chk = $mac_arr[0] . $mac_arr[1] . $mac_arr[2];

        $query = "select manuf from nic_manuf where macpre = '$mac_chk'";
        $mac_ret = $db->doSel( $query );
        if ( $db->numrows > 0 ) {
            $manuf = $mac_ret[0]['manuf'];
            debuglog( "Manufacturer: " . $manuf );
        }
    }
    
    if( strpos( $os, "man-in-the-middle") !== false ){
        $os = "";
    }

    $query = "insert into hosts(ipaddr,hostname,netbiosdomain,netbiosname,location,envtype,company,owner,product,bu,purpose,os,decom,inuse,pm,patched,poc,macaddr,manuf,model,last_seen,connfrom) "
            . "values ( '$ipaddr', '" . trim( strtolower($hostname) ) . "', '$netbiosdomain', '$netbiosname', '$location', '$envtype', '$company', '$owner', '$product', '$bu', '$purpose', "
            . "'$os', '$decom', '$inuse', '$pm', '$patched', '$poc', '". strtoupper($macaddr) ."', '$manuf', '$model', '$last_seen', '" . strtolower( $connfrom ) . "')";

    debuglog( "Executing query : " . $query );
    $ret = $db->doExec( $query );
    if ( $ret === false ) {
        debuglog( "Unable to insert record - query : " . $query );
    } else {
        debuglog( "Record updated successfully..." );
    }
}