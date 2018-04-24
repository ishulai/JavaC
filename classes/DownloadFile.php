<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "@dministrat0r", "javac");

$file = $_REQUEST['file'];

$sql = "SELECT * FROM `classes` WHERE id = '" . $file . "'";
$result = $conn->query($sql);

$filename = "";

while($row = $result -> fetch_assoc()) {
    $filename = $row['name'] . ".java";
    $myfile = fopen($row['name'] . ".java", "w");
    fwrite($myfile, $row['contents']);
    fclose($myfile);
}

header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Length: " . filesize("$filename"). ";");
header("Content-Disposition: attachment; filename= " . $filename);
header("Content-Type: application/octet-stream; "); 
header("Content-Transfer-Encoding: binary");

readfile($filename);

unlink($filename);

?>