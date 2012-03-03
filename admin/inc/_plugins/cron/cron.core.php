<?php
define("BASIC_GETINC", true);
include("../../../loader.php");

function execInBackground($cmd) {
    $svr = rtrim(php_ini_loaded_file(), 'php.ini').'php.exe ';
    if (substr(php_uname(), 0, 7) == "Windows") {
        pclose(popen("start /B ". $svr . $cmd, "r"));
    } else {
        exec($svr . $cmd . " > /dev/null &");
        ecec("exit(0)");
    }
}
?>