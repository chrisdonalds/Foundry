<?PHP
header('content-type:text/css');
header("Expires: ".gmdate("D, d M Y H:i:s", (time()+900)) . " GMT");

// grab the c parameter and ensure that it contains .css is no slashes
// this is a safety measure to prevent XSS
$c = $_GET['c'];
if(preg_match('/\//', $c) || !preg_match('/.css/', $c)){
	die('Only local CSS files allowed!');
	exit;
}

// load the content of the CSS file into the variable css, end if the
// file wasn't found.
$css = load($c);
if($css == ''){
	die('CSS File not found, sorry!');
	exit;
}
// grab all constants and store them in the array constants
preg_match_all("/\\$(\w+).*=.*\'(.*)\'/", $css, $constants);
for($i = 0; $i < count($constants[1]); $i++){
	// replace all occurrences of the constants with their values
	$css=preg_replace("/\\$".$constants[1][$i]."/", $constants[2][$i], $css);
}

//grab all REQUEST items
foreach($_GET as $key => $value){
	if($key != "" && $key != "c" && $value != "") $css = preg_replace("/\\$".$key."/", $value, $css);
}

// delete all constant definitions
//$css=preg_replace("/\\$.*=.*?;\s+/s", "", $css);

// print out the style sheet
echo $css;

function load($filelocation){
	if (file_exists($filelocation)){
		$newfile = fopen($filelocation, "r");
		$file_content = fread($newfile, filesize($filelocation));
		fclose($newfile);
		return $file_content;
	}
}
?>
