<?php

/*##########################################################################################################################################
THIS OUTPUTS AN ENTRY TO A LOG FILE WHEN NECESSARY FOR GLOBAL DEBUGGING.
JUST MAKE SURE THAT THE $level THAT YOU PASS IN IS GREATER THAN THE
$debuglevel SET HERE.  YOU CAN USE THE $debuglevel VARIABLE TO CONTROL
WHAT LEVEL OF DEBUGGING THAT YOU ARE PERFORMING.
Copyright © 2009  Tribal Paradigm, LLC – All Rights Reserved
Scott Morris - smmorris@gmail.com
##########################################################################################################################################*/

//MAKE AN ENTRY IN THE DEBUG LOG FOR THE CALLING SCRIPT.
function debuglog( $tolog, $logfile = "", $level=0 ){
    
    global $XWV;

    //IF A LOG FILE WAS NOT SPECIFIED, USE THE DEFAULT
    if(empty($logfile)){ $logfile=$XWV['deflog']; }
    $debuglevel = 0;
    if( $level >= $debuglevel ){
            $datestamp = @date( "Y-m-d H:i:s" );
            $handle = fopen( $logfile, "a" );
            fwrite( $handle, $datestamp." - ".$tolog."\n" );
            fclose( $handle );
    }
}

?>
