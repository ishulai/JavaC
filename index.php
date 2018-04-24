<!DOCTYPE html>
<head>
	<meta charset="UTF-8">
	<title>JavaC Online</title>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
	<script src="lib/codemirror.js"></script>
	<script src="mode/javascript/javascript.js"></script>
	<script src="mode/clike/clike.js"></script>
	<script src="mode/python/python.js"></script>
	<script src="addon/selection/active-line.js"></script>
	<script src="addon/edit/matchbrackets.js"></script>
	<script src="addon/search/search.js"></script>
	<script src="addon/dialog/dialog.js"></script>

	<link rel="stylesheet" href="style.css" />
	<link rel="stylesheet" href="lib/codemirror.css" />
	<link rel="stylesheet" href="theme/monokai.css" />
	<link rel="stylesheet" href="addon/dialog/dialog.css" />
</head>
<body>
	<iframe id="frame" style="display:none"></iframe>
	<ul class='custom-menu' id="projectmenu">
	  <li data-action="rename">Rename</li>
	  <li data-action="delete">Delete</li>
	  <li data-action="download">Download</li>
	</ul>
	<ul class='custom-menu' id="classmenu">
	  <li data-action="open">Open</li>
	  <li data-action="compile">Compile</li>
	  <li data-action="rename">Rename</li>
	  <li data-action="delete">Delete</li>
	  <li data-action="download">Download</li>
	</ul>
	<div id="overlay"></div>
	<div id="createproject">
		<h2>Project Name</h2>
		<input type="text" />
		<button onclick="save('project')">Save</button>
		<button onclick="cancel()">Cancel</button>
	</div>
	<div id="createclass">
		<h2>Class Name</h2>
		<input type="text" />
		<button onclick="save('class')">Save</button>
		<button onclick="cancel()">Cancel</button>
	</div>
	<div id="prompt" data-id="-1">
		<h2>Please enter a new name:</h2>
		<input type="text" />
		<button onclick="save('name')">Save</button>
		<button onclick="cancel()">Cancel</button>
	</div>
	<div id="confirm" data-id="-1">
		<h2>Are you sure you want to delete this permanently?</h2>
		<button onclick="" class="delete">Delete</button>
		<button onclick="cancel()">Cancel</button>
	</div>
	<div id="left">
		<div id="sidebar">
			<?php /*
			<h3>Files</h3>
			<ul data-index="0">
				<?php
					require "classes/FileSystem.php";

					$files = new FileSystem();

					$result = $files -> listDirectory(0);
					if($result == false) {
						echo "<ul><li class=\"empty\">This directory is empty.</li></ul>";
					} else {
						echo $result;
					}
				?>
			</ul>
			*/ ?>
			<h3>Projects</h3>
			<?php
				require "classes/FileSystem.php";
				$conn = new mysqli("localhost", "root", "@dministrat0r", "javac");
				$files = new FileSystem($conn);

				$result = $files -> listProjects();
				echo $result;
				echo "<div class=\"create\" onclick=\"create('project')\">Click Here to Create a Project</div>";
			?>
		</div>
		<div id="codepad">
			<h3>Codepad</h3>
			<div class="output"></div>
			<input type="text" placeholder="Type an expression..." />
		</div>
	</div>
	<div id="right">
		<div id="menu">
			<div class="button" onclick="compileClass(-1)">Compile</div>
			<div class="button" onclick="saveDoc()">Save</div>
			<div class="button" onclick="myCodeMirror.execCommand('find')">Find</div>
			<div class="button" style="">Close</div>
			<div class="saved">Saved</div>
		</div>
		<div id="bar"></div>
		<div id="editor"></div>
		<div id="terminal">
			<h3>Terminal Output</h3>
			<div class="code"></div>
		</div>
	</div>
	<script>
		var myCodeMirror = CodeMirror(document.getElementById("editor"), {
			mode:  "text/x-java",
			indentUnit: 4,
			tabSize: 4,
			theme: "monokai",
			smartIndent: true,
			indentWithTabs: true,
			lineNumbers: true,
			lineWrapping: true
		});

		var documents = [];
		var project = 0;
		var currentClass = 0;

		myCodeMirror.on("change", function() {
			$(".saved").hide();
			$("#count").html(myCodeMirror.getValue().split(/\r\n|\r|\n/).length);
		});

		function openClass(id) {
			classHeader(id);
			$.ajax({
				type: "POST",
				url: "classes/FileSystem.php?action=openclass",
				data: "id=" + id,
				success: function(html) {
					documents.push(CodeMirror.Doc(html, "text/x-java"));
					myCodeMirror.swapDoc(documents[documents.length - 1]);
					currentClass = id;
					$(".saved").hide();
				}
			});
		}

		function classHeader(id) {
			$.ajax({
				type: "POST",
				url: "classes/FileSystem.php?action=classheader",
				data: "id=" + id,
				success: function(html) {
					$("#bar").html(html);
				}
			});
		}

		function addClass(name) {
			$.ajax({
				type: "POST",
				url: "classes/FileSystem.php?action=addclass",
				data: "name=" + name + "&project=" + project,
				success: function(html) {
					$(".create").before("<div class=\"class\" data-class=\"" + html + "\" id=\"class-" + html + "\" onclick=\"openClass(" + html + ")\">" + name + "</div>")
					currentClass = html;
				}
			});
		}

		function addProject(name) {
			$.ajax({
				type: "POST",
				url: "classes/FileSystem.php?action=addproject",
				data: "name=" + name,
				success: function(html) {
					$(".create").before("<div class=\"project\" data-project=\"" + html + "\" id=\"project-" + html + "\" onclick=\"openProject(" + html + ")\">" + name + "</div>")
				}
			});
		}

		function openProject(id) {
			$.ajax({
				type: "POST",
				url: "classes/FileSystem.php?action=openproject",
				data: "id=" + id,
				success: function(html) {
					$("#sidebar").html("<h3><span class=\"back\" onclick=\"listProjects()\">&#x25c0; Back to Home</span></h3>" + html + "<div class=\"create\" onclick=\"create('class')\">Click Here to Create a Class</div>");
					project = id;
				}
			});
		}

		function listProjects() {
			$.ajax({
				type: "POST",
				url: "classes/FileSystem.php?action=listprojects",
				success: function(html) {
					$("#sidebar").html("<h3>Projects</h3>" + html + "<div class=\"create\" onclick=\"create('project')\">Click Here to Create a Project</div>");
					project = id;
				}
			});
		}

		function listDirectory(index) {
			if($("[data-index='" + index + "']").length) {
				$("[data-index='" + index + "']").toggle();
			} else {
				$.ajax({
					type: "POST",
					url: "classes/FileSystem.php?action=listdirectory",
					data: "index=" + index,
					success: function(html) {
						if(html == "false") {
							$("[data-folder = " + index + "]").after("<ul data-index=\"" + index + "\"><li class=\"empty\">This directory is empty.</li></ul>");
						} else {
							$("[data-folder = " + index + "]").after("<ul data-index=\"" + index + "\">" + html + "</ul>");
						}
					}
				});
			}
		}

		function create(type) {
			$("#overlay").show();
			$("#create" + type).show();
			$("#create" + type + " input").focus();
		}

		function cancel() {
			$("#overlay").hide();
			$("#createproject").hide();
			$("#createclass").hide();
			$("#prompt").hide();
			$("#confirm").hide();
			promptType = false;
			$("#prompt").attr("data-id", "-1");
		}

		function save(type) {
			$("#overlay").hide();
			$("#create" + type).hide();
			$("#prompt").hide();
			if(type == "class") {
				addClass($("#createclass input").val());
				$("#createclass input").val("")
			} else if(type == "project") {
				addProject($("#createproject input").val());
				$("#createproject input").val("")
			} else if(type == "name") {
				if(promptType == "project") {
					renameProject($("#prompt input").val(), $("#prompt").attr("data-id"));
					$("#prompt input").val("")
					$("#prompt").attr("data-id", "-1");
				} else if(promptType == "class") {
					renameClass($("#prompt input").val(), $("#prompt").attr("data-id"));
					$("#prompt input").val("");
					$("#prompt").attr("data-id", "-1");
				}
			}
		}

		$("#createclass input").keyup(function (e) {
		    if (e.keyCode == 13) {
		        save("class");
		    }
		});

		$("#createproject input").keyup(function (e) {
		    if (e.keyCode == 13) {
		        save("project");
		    }
		});

		$("#prompt input").keyup(function (e) {
		    if (e.keyCode == 13) {
		        save("name");
		    }
		});

		$("#codepad input").keyup(function (e) {
		    if (e.keyCode == 13) {
		        parseExp($("#codepad input").val());
		    }
		});

		function parseExp(str) {
			$("#codepad .output").append(str + "<br>");
			$("#codepad .output").scrollTop($('#codepad .output')[0].scrollHeight);
			$("#codepad .output").append("<span class=\"result\">" + eval(str) + "</span><br>");
			$("#codepad .output").scrollTop($('#codepad .output')[0].scrollHeight);
			$("#codepad input").val("");
		}

		function saveDoc() {
			$.ajax({
				type: "POST",
				url: "classes/FileSystem.php?action=savedoc",
				data: "id=" + currentClass + "&data=" + encodeURIComponent(myCodeMirror.getValue()),
				success: function(html) {
					$(".saved").css("display", "inline-block");
				}
			});
		}

		var selectedID = 0;

		function compileClass(id) {
			if(id == -1) {
				$.ajax({
					type: "POST",
					url: "classes/FileSystem.php?action=savedoc",
					data: "id=" + currentClass + "&data=" + encodeURIComponent(myCodeMirror.getValue()),
					success: function(html) {
						$(".saved").css("display", "inline-block");
						$.ajax({
							type: "POST",
							url: "compile.php",
							data: "classid=" + currentClass + "&projectid=" + project,
							success: function(html) {
								$("#terminal .code").html(html + "\n");
								$("#terminal .code").scrollTop($("#terminal .code").prop("scrollHeight"));
							}
						});
					}
				});
			} else {
				$.ajax({
					type: "POST",
					url: "compile.php",
					data: "classid=" + id + "&projectid=" + project,
					success: function(html) {
						$("#terminal .code").html(html + "\n");
						$("#terminal .code").scrollTop($("#terminal .code").prop("scrollHeight"));
					}
				});
			}
		}

		var promptType = false;

		function rename(type, id) {
			promptType = type;
			$("#prompt").attr("data-id", id);
			$("#prompt").show();
			$("#overlay").show();
			$("#prompt input").focus();
		}

		function delete_(type, id) {
			promptType = type;
			$("#confirm").attr("data-id", id);
			$("#confirm .delete").attr("onclick", "delete" + type + "(" + id + ")");
			$("#confirm").show();
			$("#overlay").show();
			$("#confirm input").focus();
		}

		function renameProject(name, id) {
			$.ajax({
				type: "POST",
				url: "classes/FileSystem.php?action=renameproject",
				data: "id=" + id + "&name=" + encodeURIComponent(name),
				success: function(html) {
					$("#project-" + id).html(html);
				}
			});
		}

		function renameClass(name, id) {
			$.ajax({
				type: "POST",
				url: "classes/FileSystem.php?action=renameclass",
				data: "id=" + id + "&name=" + encodeURIComponent(name),
				success: function(html) {
					$("#class-" + id).html(html);
					if(currentClass == id) {
						$("#filename").html(html + ".java");
					}
				}
			});
		}

		function deleteproject(id) {
			$.ajax({
				type: "POST",
				url: "classes/FileSystem.php?action=deleteproject",
				data: "id=" + id,
				success: function() {
					cancel();
					$("#project-" + id).remove();
				}
			});
		}

		function deleteclass(id) {
			$.ajax({
				type: "POST",
				url: "classes/FileSystem.php?action=deleteclass",
				data: "id=" + id,
				success: function() {
					cancel();
					$("#class-" + id).remove();
				}
			});
		}

		function download(type, id) {
			if(type == "project") {
				$("#frame").attr("src", "classes/DownloadProject.php?project=" + id);
			} else if(type == "class") {
				$("#frame").attr("src", "classes/DownloadFile.php?file=" + id);
			}
		}

		$("#sidebar").on("contextmenu", ".project", function (event) {
			selectedID = $(event.target).attr('data-project');
			event.preventDefault();
			$("#projectmenu").finish().toggle(100).
			css({
				top: event.pageY + "px",
				left: event.pageX + "px"
			});
		});

		$("#sidebar").on("contextmenu", ".class", function (event) {
			selectedID = $(event.target).attr('data-class');
			event.preventDefault();
			$("#classmenu").finish().toggle(100).
			css({
				top: event.pageY + "px",
				left: event.pageX + "px"
			});
		});

		$(document).bind("mousedown", function (e) {
			if (!$(e.target).parents(".custom-menu").length > 0) {
				$(".custom-menu").hide(100);
				selectedID = -1;
			}
		});

		$("#projectmenu li").click(function() {
			switch($(this).attr("data-action")) {
				case "rename": rename("project", selectedID); break;
				case "delete": delete_("project", selectedID); break;
				case "download": download("project", selectedID); break;
			}
			$(".custom-menu").hide(100);
			selectedID = -1;
		});

		$("#classmenu li").click(function() {
			switch($(this).attr("data-action")) {
				case "open": openClass(selectedID); break;
				case "compile": compileClass(selectedID); break;
				case "rename": rename("class", selectedID); break;
				case "delete": delete_("class", selectedID); break;
				case "download": download("class", selectedID); break;
			}
			$(".custom-menu").hide(100);
			selectedID = -1;
		});

		$(window).bind('keydown', function(event) {
		    if (event.ctrlKey || event.metaKey) {
		        switch (String.fromCharCode(event.which).toLowerCase()) {
		        case 's':
		            event.preventDefault();
		            saveDoc();
		            break;
		        /*case 'f':
		            event.preventDefault();
		            alert('ctrl-f');
		            break;
		        case 'g':
		            event.preventDefault();
		            alert('ctrl-g');
		            break;*/
		        }
		    }
		});
</script>
</body>
</html>