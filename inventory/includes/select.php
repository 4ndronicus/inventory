<?php

/*
 * $selection_identifier - named such that when we post values, we know which table
 * we need to update
 * $selected_value - the current value of the field in the row in the database
 * $selection_array - the list of field names along with their human-readable counterparts
 */

function createSelect($selection_identifier, $selected_value, $selection_array) {

    debuglog("Entering " . __METHOD__);

    global $XWV;
    $selObj = "";
    $vars['sel_id'] = $selection_identifier;

    debuglog("Selection id: " . $selection_identifier);
    debuglog("Selected value: " . $selected_value);

    $selRows = "";
    $vars['sel'] = "";
    $vars['sel_list'] = "";
    foreach ($selection_array as $key => $option) {
//        debuglog( "Selection option: " . $option );
        if ($selected_value === $option) {
            $vars['sel'] = "selected";
        }
        $vars['db_field'] = $option;
//        $out = var_export( $vars, true );
//        debuglog( "\$vars = " . $out );
        $vars['sel_list'] .= replace($vars, rf($XWV['tpl'] . "/sel_row.html"));
//        debuglog( "\$vars[sel_list] = " . $vars['sel_list'] );
        $vars['sel'] = "";
    }
    $selObj .= replace($vars, rf($XWV['tpl'] . "/generic_sel.html"));
    return $selObj;
}

?>