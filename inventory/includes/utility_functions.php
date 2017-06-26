<?php

// displays a message then exits
function showMsg($msg) {
    global $XWV;
    $vars['msg'] = $msg;
    $HTML = replace($vars, rf($XWV['tpl'] . "/error.html"));
    echo $HTML;
    exit;
}

function var_out( $var ){
    echo "<pre>\n";
    var_dump( $var );
    echo "</pre>\n";
}

function show_globals(){
    echo "Cookie:<br>\n";
    var_out( $_COOKIE );
    
    echo "Environment:<br>\n";
    var_out( $_ENV );
    
    echo "Files:<br>\n";
    var_out( $_FILES );
    
    echo "Get:<br>\n";
    var_out( $_GET );
    
    echo "Post:<br>\n";
    var_out( $_POST );
    
    echo "Request:<br>\n";
    var_out( $_REQUEST );
    
    echo "Server:<br>\n";
    var_out( $_SERVER );
    
    echo "Session:<br>\n";
    var_out( $_SESSION );
}

?>