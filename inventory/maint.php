<?php
/*
 * Need to add 'architecture' and 'note' to the hosts table
 */

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
$class_c = $ip_arr[0] . "." . $ip_arr[1] . "." . $ip_arr[2] . ".";

debuglog( "Current Class C: " . $class_c );

/*
 * Figure out the location and environment if applicable
 */
switch ( $class_c ) {
    case "172.23.245.":
        $envtype = "Mgmt";
    case "172.23.240.":
    case "172.23.241.":
    case "172.23.244.":
    case "172.23.246.":
        $location = "UK";
        break;
    case "10.53.140.":
        $envtype = "Mgmt";
    case "10.53.128.":
    case "10.255.6.":
    case "10.53.129.":
    case "10.53.132.":
    case "10.53.136.":
    case "10.53.144.":
    case "172.16.90.":
    case "172.16.91.":
    case "172.16.92.":
    case "172.16.93.":
    case "172.16.94.":
    case "172.16.95.":
    case "172.23.48.":
    case "172.23.49.":
    case "172.23.50.":
    case "172.23.80.":
    case "172.23.81.":
    case "172.23.82.":
    case "172.23.83.":
    case "172.23.84.":
    case "172.23.85.":
    case "172.23.86.":
        $location = "New Jersey";
        break;
    case "10.61.140.":
    case "10.61.141.":
        $envtype = "Mgmt";
    case "10.60.68.":
    case "10.60.69.":
    case "10.60.70.":
    case "10.60.71.":
    case "10.60.72.":
    case "10.61.129.":
    case "10.61.130.":
    case "10.61.131.":
    case "10.61.144.":
    case "172.23.88.":
    case "172.23.92.":
    case "172.23.93.":
    case "172.23.128.":
    case "172.23.129.":
        $location = "Lehi";
        break;
    case "172.23.5.":
    case "172.23.6.":
    case "172.23.7.":
    case "172.23.8.":
    case "172.23.9.":
    case "172.23.10.":
    case "172.23.11.":
    case "172.23.12.":
    case "172.23.13.":
    case "172.23.14.":
    case "172.23.15.":
        $location = "Lehi";
        $purpose = "Workstation";
        break;
    case "172.23.249.":
        $location = "Germany";
        break;
    case "10.61.156.":
        $location = "Australia";
        break;
}

debuglog("Location: " . $location );

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
 * Take a note of the user we connected with, if sent
 */
$connuser = $_REQUEST['connuser'];

$location = "";
$envtype = "";
$owner = "";
$product = "";
if ( isblank( $purpose ) ) {
    $purpose = "";
}
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
debuglog( "Is server in use, and do we care?" );
if ( $inuse === 0 && !isblank( $_REQUEST['alive'] ) ) {
    debuglog( "No it is not. Disabling and continuing." );
    $query = "update hosts set inuse=0, last_seen = '$last_seen', connfrom = '$connfrom' where ipaddr = '" . $ipaddr . "'";
//    $query = "update hosts set hostname='', netbiosdomain='', netbiosname='', location='', envtype='', company='', owner='', product='', "
//            . "bu='', purpose='', os='', decom=false, inuse=false, pm=false, patched='0000-00-00', poc='', macaddr='', manuf='', model='', "
//            . "last_seen='0000-00-00'  where ipaddr = '" . $ipaddr . "' limit 1";
    debuglog( "Executing query: " . $query );
    $ret = $db->doExec( $query );
    if ( $ret === false ) {
        debuglog( "Unable to update record - query : " . $query );
    } else {
        debuglog( "Record updated successfully..." );
    }
    /*
     * Our work here is done.
     */
    exit;
}

debuglog( "Yes, it is in use or we do not care." );

/*
 * If we have a hostname, try and work with that, otherwise use the IP address.
 * Just because the IP address may be blank, or the hostname may be blank.  If
 * there is a hostname, match on that.  If there is no hostname, but there is an
 * IP address, go ahead and use that.
 */
//if ( strlen( $hostname ) > 0 ) {
//    $where = "where hostname = '" . $hostname . "'";
//} else {
//    $where = "";
//}

$query = "select * from hosts where ipaddr = '" . $ipaddr . "'";
debuglog( "query: " . $query );

$ret = $db->doSel( $query );
/*
 * Check to see if that host exists either by IP or hostname
 */
if ( $ret != false && $db->numrows > 0 ) {
    /*
     * If no error occurred, and we found a record, let's pull all of the info 
     * out of it and clear it out.  Then, we can add it back in with the proper
     * record if it has changed.
     */
    debuglog( $db->numrows . " existing record(s) for hostname " . $hostname . "/ip $ipaddr found. Gathering data for new record." );

    /*
     * If we found more than one record, that shouldn't happen.
     */
    if ( $db->numrows > 1 ) {
        debuglog( "############################ Too many records found for this host. Can only update one!" );
    }

    /*
     * Grab the first record, and pull all of the information out of it
     */
    $rec = $ret[0];
    $location = $rec['location'];
    $envtype = $rec['envtype'];
    $owner = $rec['owner'];
    $product = $rec['product'];
    $decom = $rec['decom'];
    $pm = $rec['pm'];
    $patched = $rec['patched'];
    $poc = $rec['poc'];

    /*
     * We only care about the 'inuse' flag if we have passed in a value for it.
     * If there is no value passed in, use the one from the database.
     */
    if ( isblank( $_REQUEST['alive'] ) ) {
        debuglog( "No 'alive' flag passed in from the request. Using the one from the hosts table." );
        $inuse = $rec['inuse'];
        debuglog( "Inuse flag from hosts table: " . $inuse );
    }
    if ( isblank( $purpose ) ) {
        $purpose = $rec['purpose'];
    }
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

//    if ( isblank( $last_seen ) && strlen( $rec['last_seen'] ) > 1 ) {
//        $last_seen = $rec['last_seen'];
//    }

    debuglog( "Data retrieved for insertion into new record." );

    $hostid = $rec['hostid'];

    debuglog( "Clearing out old record data for hostid: " . $hostid );

    /*
     * Clear out the old record data
     */
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

    debuglog( "Searching for records with IP address of '$ipaddr'" );

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

    if ( strpos( $os, "man-in-the-middle" ) !== false ) {
        $os = "";
    }

    debuglog( "OS before: " . $os );
    $os = cleanOs( $os );
    debuglog( "OS after: " . $os );

    /*
     * Must have IP address to continue
     */

    if ( strlen( $ipaddr ) > 0 ) {
        /*
         * Check to see if a record exists for that host already
         */
        if ( $ret === false && $db->numrows == 0 ) { // if not, add one
            debuglog( "IP address not found... inserting..." );

            $query = "insert into hosts(ipaddr,hostname,netbiosdomain,netbiosname,location,envtype,company,owner,product,bu,purpose,os,decom,inuse,pm,patched,poc,macaddr,manuf,model,last_seen,connfrom,connuser) "
                    . "values ( '$ipaddr', '" . trim( strtolower( $hostname ) ) . "', '$netbiosdomain', '$netbiosname', '$location', '$envtype', '$company', '$owner', '$product', '$bu', '$purpose', "
                    . "'$os', '$decom', '$inuse', '$pm', '$patched', '$poc', '" . strtoupper( $macaddr ) . "', '$manuf', '$model', '$last_seen', '" . strtolower( $connfrom ) . "', '" . strtolower( $connuser ) . "')";

            debuglog( "Executing query : " . $query );
            $ret = $db->doExec( $query );
            if ( $ret === false ) {
                debuglog( "Unable to insert record - query : " . $query );
            } else {
                debuglog( "Record updated successfully..." );
            }
        } else {

            /*
             * If it does exist, update that record with the information we have compiled about that host.
             */
            $query = "update hosts set hostname='" . trim( strtolower( $hostname ) ) . "', netbiosdomain='$netbiosdomain', location='$location', envtype='$envtype', company='$company', owner='$owner', "
                    . "product='$product', bu='$bu', purpose='$purpose', os='$os', decom='$decom', inuse=$inuse, pm='$pm', patched='$patched', poc='$poc', macaddr='" . strtoupper( $macaddr ) . "', "
                    . "manuf='$manuf', model='$model', last_seen='$last_seen', connfrom='" . strtolower( $connfrom ) . "', connuser='" . strtolower( $connuser ) . "' where ipaddr='$ipaddr'";
            debuglog( "Executing query: " . $query );
            $ret = $db->doExec( $query );

            if ( $ret === false ) {
                debuglog( "Unable to update record - query : " . $query );
            } else {
                debuglog( "Record updated successfully..." );
            }
        }
    } else {
        debuglog( "No IP address found. Not inserting." );
    }
} else {
    /*
     * We did not find any record for that host at all
     */

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

    if ( strpos( $os, "man-in-the-middle" ) !== false ) {
        $os = "";
    }

    /*
     * The IP address is required.  No IP, no record.
     */
    if ( strlen( $ipaddr ) > 0 ) {
        
        debuglog( "OS before: " . $os );
        $os = cleanOs( $os );
        debuglog( "OS after: " . $os );

        $query = "insert into hosts(ipaddr,hostname,netbiosdomain,netbiosname,location,envtype,company,owner,product,bu,purpose,os,decom,inuse,pm,patched,poc,macaddr,manuf,model,last_seen,connfrom,connuser) "
                . "values ( '$ipaddr', '" . trim( strtolower( $hostname ) ) . "', '$netbiosdomain', '$netbiosname', '$location', '$envtype', '$company', '$owner', '$product', '$bu', '$purpose', "
                . "'$os', '$decom', '$inuse', '$pm', '$patched', '$poc', '" . strtoupper( $macaddr ) . "', '$manuf', '$model', '$last_seen', '" . strtolower( $connfrom ) . "', '" . strtolower( $connuser ) . "')";

        debuglog( "Executing query : " . $query );
        $ret = $db->doExec( $query );
        if ( $ret === false ) {
            debuglog( "Unable to insert record - query : " . $query );
        } else {
            debuglog( "Record updated successfully..." );
        }
    } else {
        debuglog( "No IP address found. Not inserting." );
    }
}