<?php

/* ##########################################################################################################################################

  INCLUDE FILE THAT CONTAINS ALL OF THE PATHS AND GLOBAL CONFIGURATION SETTINGS THAT WE WILL NEED THROUGHOUT THE PROJECT.

  Copyright © 2009 - 2012 by Scott Morris – All Rights Reserved
  Scott Morris - smmorris@gmail.com

  ########################################################################################################################################## */

/*    SET UP VARIABLES THAT WILL BE NEEDED BY EVERY SCRIPT    */

$XWV['web'] = dirname( __FILE__ );                     //WEB ROOT OF THE PROJECT
$XWV['inc'] = "$XWV[web]/includes";                    //INCLUDE DIRECTORY
$XWV['logdir'] = "$XWV[web]/logs";                     //LOG DIRECTORY TO WHERE ALL LOGS WILL BE WRITTEN
$XWV['deflog'] = "$XWV[logdir]/generic_output_log";    //DEFAULT LOG FILE FOR USE WHEN NO LOG IS SPECIFIED
$XWV['layout'] = "$XWV[web]/layout";                   //LOCATION FOR THE LAYOUT ELEMENTS
$XWV['tpl'] = "$XWV[web]/html_templates";              //LOCATION FOR ANY TEMPLATES USED

$vars = array();

/*    SET UP INCLUDES THAT WILL BE NEEDED BY EVERY SCRIPT    */

require_once( "$XWV[inc]/debug.php" );                  //INCLUDE THE DEBUGGING CODE THAT WE'LL WANT ON EVERY PAGE
require_once( "$XWV[inc]/template.php" );               //LOAD UP THE TEMPLATING FUNCTIONALITY (HOW CAN THIS BE PUT INTO A CLASS?)
require_once( "$XWV[inc]/dbconn.class.php" );           //LOAD UP THE DATABASE CONNECTION OBJECT
require_once( "$XWV[inc]/sanitize.php" );               //LOAD UP THE SANITIZING FUNCTIONALITY
require_once( "$XWV[inc]/utility_functions.php" );      //LOAD UP THE UTILITY FUNCTIONALITY
require_once( "$XWV[inc]/select.php" );                 //LOAD UP THE HTML SELECT OBJECT
require_once( "$XWV[inc]/classBase.php" );              //LOAD UP THE PHP BASE CLASS
require_once( "$XWV[inc]/classShowTable.php" );         //LOAD UP THE SHOW TABLE CLASS
require_once( "$XWV[inc]/classCSV.php" );               //LOAD UP THE CSV MANAGEMENT CLASS
//
//ALMOST EVERY PAGE WILL NEED A DATABASE OBJECT, SO LET'S JUST CREATE THAT BAD DOG HERE.
$db = new dbConn();
?>
