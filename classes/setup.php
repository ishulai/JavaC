<?php
global $conn = new mysqli("localhost", "root", "@dministrat0r", "javac");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
?>