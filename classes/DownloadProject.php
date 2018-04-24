<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "@dministrat0r", "javac");

$project = $_REQUEST['project'];
$files = array();
$name = $conn -> query("SELECT `name` FROM `projects` WHERE id = '" . $project . "'") -> fetch_object() -> name;

$sql = "SELECT * FROM `classes` WHERE project = '" . $project . "'";
$result = $conn->query($sql);

mkdir($name);

while($row = $result -> fetch_assoc()) {
    $myfile = fopen($name . "/" . $row['name'] . ".java", "w");
    array_push($files, $name . "/" . $row['name'] . ".java");
    fwrite($myfile, $row['contents']);
    fclose($myfile);
}

$packageContent = "#BlueJ package file
objectbench.height=100
objectbench.width=658
package.editor.height=400
package.editor.width=879
package.editor.x=604
package.editor.y=476
package.showExtends=true
package.showUses=true
project.charset=UTF-8
readme.editor.height=700
readme.editor.width=900
readme.editor.x=0
readme.editor.y=0
";

$myfile = fopen($name . "/package.bluej", "w");
array_push($files, $name . "/package.bluej");
fwrite($myfile, $packageContent);
fclose($myfile);
 
$valid_files = array();
if(is_array($files)) {
    foreach($files as $file) {
        if(file_exists($file)) {
            $valid_files[] = $file;
        }
    }
}
 
if(count($valid_files > 0)){
    $zip = new ZipArchive();
    $zip_name = $name . ".zip";
    
    if($zip->open($zip_name, ZIPARCHIVE::CREATE)!==TRUE){
        $error .= "* Sorry ZIP creation failed at this time";
    }
 
    foreach($valid_files as $file){
        $zip->addFile($file);
    }
 
    $zip->close();
    if(file_exists($zip_name)){
        // force to download the zip
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private",false);
        header('Content-type: application/zip');
        header('Content-Disposition: attachment; filename="'.$zip_name.'"');
        readfile($zip_name);
        // remove zip file from temp path
        unlink($zip_name);
    }
 
} else {
    echo "No valid files to zip";
    exit;
}

$files = glob($name . '/*'); // get all file names
foreach($files as $file) { // iterate files
    if(is_file($file)) {
        unlink($file); // delete file
    }
}

rmdir($name);

?>