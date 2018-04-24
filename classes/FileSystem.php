<?php

class FileSystem {
	private $conn;

	function __construct(mysqli $conn) {
		$this->conn = $conn;
	}

	function listDirectory($index) {
		$sql1 = "SELECT * FROM `folders` WHERE `parent` = '" . strval($index) . "'";
		$sql2 = "SELECT * FROM `files` WHERE `parent` = '" . strval($index) . "'";
		$folders = $conn->query($sql1);
		$files = $conn->query($sql2);

		$html = "";

		if($folders->num_rows > 0 || $files->num_rows > 0) {
		    while($row = $folders->fetch_assoc()) {
		        $html .= "<li class=\"folder\" data-folder=\"" . $row['id'] . "\" onclick=\"listDirectory(" . $row['id'] . ")\">" . $row['name'] . "</li>";
		    }
		    while($row = $files->fetch_assoc()) {
		        $html .= "<li class=\"file\" data-files=\"" . $row['id'] . "\" onclick=\"openFile(" . $row['id'] . ")\">" . $row['name'] . "." . $row['extension'] . "</li>";
		    }
		    return $html;
		} else {
		    return false;
		}
	}

	function openClass($id) {
		$sql = "SELECT * FROM `classes` WHERE `id` = '" . $id . "' LIMIT 1";
		$result = $this -> conn -> query($sql);
		$html = "";
	    while($row = $result -> fetch_assoc()) {
	        $html .= $row['contents'];
	    }
	    return $html;
	}

	function listClasses($project) {
		$sql = "SELECT * FROM `classes` WHERE `project` = '" . $project . "'";
		$result = $this -> conn -> query($sql);
		$html = "";
	    while($row = $result -> fetch_assoc()) {
	    	if($row['contents'] == $row['lastcompiled']) {
	    		$compiled = " compiled";
	    	} else {
	    		$compiled = "";
	    	}
	        $html .= "<div class=\"class" . $compiled . "\" data-class=\"" . $row['id'] . "\" id=\"class-" . $row['id'] . "\" onclick=\"openClass(" . $row['id'] . ")\">" . $row['name'] . "</div>";
	    }
	    return $html;
	}

	function listProjects() {
		$sql = "SELECT * FROM `projects` ORDER BY name";
		$result = $this -> conn -> query($sql);
		$html = "";
	    while($row = $result -> fetch_assoc()) {
	        $html .= "<div class=\"project\" data-project=\"" . $row['id'] . "\" id=\"project-" . $row['id'] . "\" onclick=\"openProject(" . $row['id'] . ")\">" . $row['name'] . "</div>";
	    }
	    return $html;
	}

	function addClass($name, $project) {
		$sql = "INSERT INTO `classes` (name, project) VALUES ('" . $name . "', '" . $project . "')";
		$this -> conn -> query($sql);
		return $this -> conn -> insert_id;
	}

	function addProject($name) {
		$sql = "INSERT INTO `projects` (name) VALUES ('" . $name . "')";
		$this -> conn -> query($sql);
		return $this -> conn -> insert_id;
	}

	function saveDoc($id, $contents) {
		$sql = "UPDATE `classes` SET `contents` = '" . $this -> conn -> real_escape_string($contents) . "' WHERE `id` = " . $id;
		$this -> conn -> query($sql);
		return $sql;
	}

	function renameProject($id, $name) {
		$sql = "UPDATE `projects` SET `name` = '" . $name . "' WHERE id = " . $id;
		$this -> conn -> query($sql);
		return $name;
	}

	function renameClass($id, $name) {
		$sql = "UPDATE `classes` SET `name` = '" . $name . "' WHERE id = " . $id;
		$this -> conn -> query($sql);
		return $name;
	}

	function deleteProject($id) {
		$sql = "DELETE FROM `projects` WHERE id = " . $id;
		$this -> conn -> query($sql);
	}

	function deleteClass($id) {
		$sql = "DELETE FROM `classes` WHERE id = " . $id;
		$this -> conn -> query($sql);
	}

	function classHeader($id) {
		$sql = "SELECT * FROM `classes` WHERE id = " . $id ." LIMIT 1";
		$result = $this -> conn -> query($sql);
		$html = "";
	    while($row = $result -> fetch_assoc()) {
	    	$lines_arr = preg_split('/\n|\r/',$row['contents']);
			$num_newlines = count($lines_arr); 
	        $html = "<span id=\"filename\">" . $row['name'] . ".java</span> - <span id=\"count\">" . $num_newlines . "</span> Lines";
	    }
	    return $html;
	}
}

$conn = new mysqli("localhost", "root", "@dministrat0r", "javac");

if($_GET['action']) {
	$action = $_GET['action'];
	$files = new FileSystem($conn);
	if($action == "listdirectory") {
		$html = $files->listDirectory($_REQUEST['index']);
		if($html != "") {
			echo $files->listDirectory($_REQUEST['index']);
		} else {
			echo "false";
		}
	} else if($action == "openclass") {
		echo $files->openClass($_REQUEST['id']);
	} else if($action == "openproject") {
		echo $files->listClasses($_REQUEST['id']);
	} else if($action == "listprojects") {
		echo $files->listProjects();
	} else if($action == "addclass") {
		echo $files->addClass($_REQUEST['name'], $_REQUEST['project']);
	} else if($action == "addproject") {
		echo $files->addProject($_REQUEST['name']);
	} else if($action == "savedoc") {
		echo $files->saveDoc($_REQUEST['id'], $_REQUEST['data']);
	} else if($action == "renameproject") {
		echo $files->renameProject($_REQUEST['id'], $_REQUEST['name']);
	} else if($action == "renameclass") {
		echo $files->renameClass($_REQUEST['id'], $_REQUEST['name']);
	} else if($action == "deleteproject") {
		$files->deleteProject($_REQUEST['id']);
	} else if($action == "deleteclass") {
		$files->deleteClass($_REQUEST['id']);
	} else if($action == "classheader") {
		echo $files->classHeader($_REQUEST['id']);
	}
}

?>