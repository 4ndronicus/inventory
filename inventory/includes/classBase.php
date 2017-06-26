<?php

/**
 * Gives logging and database access to any class that extends this one
 */
class classBase {

    public function __construct() {
        
    }

    public function log($level, $to_log, $which = "dbg") {

        global $XWV;

        $logging_level = 2;

        // IF THE SPECIFIED LOGGING LEVEL IS EQUAL TO OR LESS THAN THE
        // LOGGING LEVEL THAT WE WANT, LET'S GO AHEAD AND WRITE IT TO THE LOG
        if ($level <= $logging_level) {

            switch ($level) {
                case 0:
                    $to_log = "CRITICAL: $to_log";
                    break;
                case 1:
                    $to_log = "INFO: $to_log";
                    break;
                case 2:
                    $to_log = "DEBUG: $to_log";
                    break;
            }

            // WHICH LOG DO WE WANT TO WRITE IT TO? 'dbg' IS JUST A GENERIC
            // OUTPUT LOG - IT IS NOT FORMATTED
            // THE 'err' LOG IS THE ERROR LOG SPECIFIED IN THE PHP.INI FILE.
            if ($which === "dbg") {
                $handle = fopen($XWV['deflog'], 'a+');
                $now = @date("Y-m-d H:i:s");
                fwrite($handle, "$now - $to_log\n");
            } else {
                @error_log($to_log);
            }
        }
    }

}
