<?php
/*
AUTOCOMPLETE INPUT PLUG-IN
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================
*/

function updateAutocomplete($element, $table, $fieldset, $crit, $order = ""){
    if($element != '' && $table != '' && $fieldset != ''){
        // get the data
        $datarec = getRec($table, "id, {$fieldset} as field", $crit, $order, "");
        $datarec = flattenDBArray($datarec, "id", "field");
        
        // build the js code
        $jscode = "var {$element} = [\"".join('", "', $datarec)."\"];\n\n";

        // make sure the localdata.js file is writable
        $file = SITE_PATH.ADMIN_FOLDER.PLUGINS_FOLDER."autocomplete/localdata.js";
        chmod2($file, "0777");

        // get the current file contents
        $contents = @file_get_contents("localdata.js");

        // replace js code segment with new code
        if(strpos($contents, "var {$element} =") !== false){
            $contents = preg_replace("var {$element} = (.)+;", $jscode, $contents);
        }else{
            $contents .= (($contents != '') ? "\n\n" : "").$jscode;
        }

        // write the contents back to the file
        @file_put_contents($file, $contents);
    }
}
?>
