<?

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "@dministrat0r", "javac");

$classid = $_REQUEST['classid'];
$projectid = $_REQUEST['projectid'];

$unique = md5(uniqid(rand(), true));
mkdir("temp/" . $unique);

$sql = "SELECT * FROM `classes` WHERE project = '" . $projectid . "'";

$result = $conn->query($sql);

$names = array();
$class = "";

$noerrors = true;

while($row = $result -> fetch_assoc()) {
    array_push($names, $row['name']);
	$myfile = fopen("temp/" . $unique . "/" . $row['name'] . ".java", "w") or die("Unable to open file!");
	fwrite($myfile, $row['contents']);
	fclose($myfile);
	if($row['id'] == $classid) {
		$class = $row['name'];
	}
}

exec("cd temp/" . $unique . "; javac " . $class . ".java 2>&1", $output, $resultCode); 
if($resultCode === 0) {
	$result = shell_exec("cd temp/" . $unique . "; javap -protected " . $class . ".class 2>&1");
	foreach(preg_split("/((\r?\n)|(\r\n?))/", $result) as $line) {
	    if(preg_match("/\s+?(public|protected|private|static|\s) +[\w\<\>\[\].]+\s+(\w+) *\([^\)]*\) *(\{?|[^;])/", $line)) {
	    	echo $line . "\n";
	    }
	} 
} else {
	echo implode("\n", $output);
}

function deleteDirectory($dir) { 
    if (!file_exists($dir)) { return true; }
    if (!is_dir($dir) || is_link($dir)) {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item) { 
        if ($item == '.' || $item == '..') { continue; }
        if (!deleteDirectory($dir . "/" . $item, false)) { 
            chmod($dir . "/" . $item, 0777); 
            if (!deleteDirectory($dir . "/" . $item, false)) return false; 
        }; 
    } 
    return rmdir($dir); 
}

deleteDirectory("temp/" . $unique);

?>