<?php
	ob_start ();

	require ('includes/global_functions.php');
	require ('includes/class.dropshadow.php');
	require ('includes/framework_functions.php');

	session_set_save_handler ("open_session", "close_session", "read_session", "write_session",
		"destroy_session", "session_gc");
	session_start ();

	if ($_SESSION["ValidLogin"] != 1)
		header ("location: login.php?r=" . $_SERVER["PHP_SELF"]);

	// command lines for exec() unzip call
	$ptokens = explode ($_SERVER["PATH"], ":");
	if (count($ptokens) > 0) {
		// linux...
		$command = "unzip -o ";
		$command_suffix = "";
	}
	else {
		// windows...
		$command = '..\..\bin\uz.bat '; 
		$command_suffix = ' \n\n';
	}


	/*
	 * UPDATE DB AS NECESSARY
	 */
	if ($page_action == "addComment") {
		$sql = "INSERT INTO AlbumComments (AlbumItemID, CommentSubject, CommentBody, CommentAuthor, CreatedDate) VALUES (" .
			$_GET["iid"] . ", '', '" . $_POST["comment_text"] . "', '" . $_SESSION["UserID"] . "', '" . date($MYSQL_DATE_FORMAT) . "')";
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		$sql = "UPDATE Galleries SET ModifiedDate = '" . date($MYSQL_DATE_FORMAT) . "' WHERE GalleryID=" . $_GET["gid"];
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		$sql = "UPDATE Albums SET ModifiedDate = '" . date($MYSQL_DATE_FORMAT) . "' WHERE AlbumID=" . $_GET["aid"];
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
	}
	else if ($page_action == "addGallery") {
		//echo "add gallery here";
		if (isset($_POST["is_public"]))
			$pub = 1;
		else
			$pub = 0;
		$sql = "INSERT INTO Galleries (IsFamilySite, MultiSite, IsPublic, GalleryTitle, GalleryDescription, CreatedBy, CreatedDate, ModifiedDate) VALUES (0, 1, " .
			$pub . ", '" . $_POST["gallery_name"] . "', '" . $_POST["gallery_description"] . "', '" . $_SESSION["UserID"] . "', '" .
			date ($MYSQL_DATE_FORMAT) . "', '" . date ($MYSQL_DATE_FORMAT) . "')";
		//echo $sql;
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
	}
	/*
	 * Add images to an album
	 */
	else if ($page_action == "addImages") {
		$basedir = pathinfo (__FILE__);
		$basedir = $basedir["dirname"];
		//echo $basedir . "<br>";
		$exec_path = $basedir . "/resources/bin";
		$temp_dir = $basedir . "/resources/temp";
		//echo $exec_path . "<br>";
		chdir ($exec_path);
		$dest_path = "../galleries";
		//echo $dest_path . "<br>";
		if (!file_exists($dest_path))
			mkdir($dest_path);
		$dest_path = $dest_path . "/" . $_GET["aid"];
		if (!file_exists($dest_path))
			mkdir($dest_path);
		$temp_dir = $temp_dir . "/" . $_GET["aid"];
		if (!file_exists ($temp_dir))
			mkdir ($temp_dir);
		$fe = getFileExtension ($_FILES["album_files"]["name"]);
		$temp_file = $temp_dir . "/" . $_FILES["album_files"]["name"];
		if (move_uploaded_file ($_FILES["album_files"]["tmp_name"], $temp_file)) {
			chdir ($temp_dir);
			if ($fe == "zip") {
				// construct command
				$cmd = $command . '"' . $_FILES["album_files"]["name"] . '"' . $command_suffix;
				// execute
				$unzip_out = exec($cmd);
				//echo "<br>" . $cmd . "<br>" . $unzip_out;
				unlink ($temp_file);
				$handle = opendir ($temp_dir);
				while ($file = readdir ($handle)) {
					if ($file != "." && $file != "..") {
						$ds = new dropShadow ();
						// resize to 800 px
						$ds->loadImage ($file);
						$ds->resizeToSize (800, 0);
						$ds->saveFinal ("../" . $dest_path . "/" . $file);
						// create thumbnail
						$ds->resizeToSize (80, 0);
						$ds->saveFinal ("../" . $dest_path . "/t_" . $file);
						$sql = "INSERT INTO AlbumItems (AlbumID, AlbumItemAuthor, CreatedDate, FileName, FileExtension, ModifiedDate) VALUES (" .
							$_GET["aid"] . ",'" . $_SESSION["UserID"] . "','" . date ($MYSQL_DATE_FORMAT) . 
							"','" . $file . "','" . getFileExtension($file) . "','" . date($MYSQL_DATE_FORMAT) . "')";
						mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
						unlink ($file);
					}
				}
				closedir ($handle);
				rmdir ($temp_dir);
			}
			else {
				$file = $_FILES["album_files"]["name"];
				$ds = new dropShadow ();
				// resize to 800 px
				$ds->loadImage ($file);
				$ds->resizeToSize (800, 0);
				$ds->saveFinal ("../../galleries/" . $_GET["aid"] . "/" . $file);
				// create thumbnail
				$ds->resizeToSize (80, 0);
				$ds->saveFinal ("../../galleries/" . $_GET["aid"] . "/t_" . $file);
				unlink ($file);
				$sql = "INSERT INTO AlbumItems (AlbumID, AlbumItemAuthor, CreatedDate, FileName, FileExtension, ModifiedDate) VALUES (" .
					$_GET["aid"] . ",'" . $_SESSION["UserID"] . "','" . date ($MYSQL_DATE_FORMAT) . 
					"','" . $_FILES["album_files"]["name"] . "','" . $fe . "','" . date($MYSQL_DATE_FORMAT) . "')";
				mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
				rmdir ($temp_dir);
			}
		}
	}
	else if ($page_action == "addAlbum") {
		// insert new album, get id
		$cd = date ($MYSQL_DATE_FORMAT);
		$sql = "INSERT INTO Albums (GalleryID, AlbumTitle, AlbumDescription, CreatedBy, CreatedDate, ModifiedDate) VALUES (" .
			$_POST["gid"] . ",'" . $_POST["album_name"] . "','" . $_POST["album_description"] . "','" . $_SESSION["UserID"] . 
			"','" . $cd . "', '" . $cd . "')";
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		$sql = "SELECT @@IDENTITY AlbumID";
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		$row = mysql_fetch_array ($rs);
		$_GET["gid"] = $_POST["gid"];
		$_GET["aid"] = $row["AlbumID"];


		$basedir = pathinfo (__FILE__);
		$basedir = $basedir["dirname"];
		$exec_path = $basedir . "/resources/bin";
		$temp_dir = $basedir . "/resources/temp";
		chdir ($exec_path);
		$dest_path = "../galleries";
		if (!file_exists($dest_path))
			mkdir($dest_path);
		$dest_path = $dest_path . "/" . $_GET["aid"];
		if (!file_exists($dest_path))
			mkdir($dest_path);
		$temp_dir = $temp_dir . "/" . $_GET["aid"];
		if (!file_exists ($temp_dir))
			mkdir ($temp_dir);
		$fe = getFileExtension ($_FILES["album_files"]["name"]);
		$temp_file = $temp_dir . "/" . $_FILES["album_files"]["name"];
		if (move_uploaded_file ($_FILES["album_files"]["tmp_name"], $temp_file)) {
			chdir ($temp_dir);
			if ($fe == "zip") {
				// construct command
				$cmd = $command . $_FILES["album_files"]["name"] . $command_suffix;
				// execute
				$unzip_out = exec($cmd);
				//echo "<br>" . $cmd . "<br>" . $unzip_out;
				unlink ($temp_file);
				$handle = opendir ($temp_dir);
				while ($file = readdir ($handle)) {
					if ($file != "." && $file != "..") {
						$ds = new dropShadow ();
						// resize to 800 px
						$ds->loadImage ($file);
						$ds->resizeToSize (800, 0);
						$ds->saveFinal ("../" . $dest_path . "/" . $file);
						// create thumbnail
						$ds->resizeToSize (80, 0);
						$ds->saveFinal ("../" . $dest_path . "/t_" . $file);
						$sql = "INSERT INTO AlbumItems (AlbumID, AlbumItemAuthor, CreatedDate, FileName, FileExtension, ModifiedDate) VALUES (" .
							$_GET["aid"] . ",'" . $_SESSION["UserID"] . "','" . date ($MYSQL_DATE_FORMAT) . 
							"','" . $file . "','" . getFileExtension($file) . "','" . date($MYSQL_DATE_FORMAT) . "')";
						mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
						unlink ($file);
					}
				}
				closedir ($handle);
				rmdir ($temp_dir);
			}
			else {
				$file = $_FILES["album_files"]["name"];
				$ds = new dropShadow ();
				// resize to 800 px
				$ds->loadImage ($file);
				$ds->resizeToSize (800, 0);
				$ds->saveFinal ("../../galleries/" . $_GET["aid"] . "/" . $file);
				// create thumbnail
				$ds->resizeToSize (80, 0);
				$ds->saveFinal ("../../galleries/" . $_GET["aid"] . "/t_" . $file);
				unlink ($file);
				$sql = "INSERT INTO AlbumItems (AlbumID, AlbumItemAuthor, CreatedDate, FileName, FileExtension, ModifiedDate) VALUES (" .
					$_GET["aid"] . ",'" . $_SESSION["UserID"] . "','" . date ($MYSQL_DATE_FORMAT) . 
					"','" . $_FILES["album_files"]["name"] . "','" . $fe . "','" . date($MYSQL_DATE_FORMAT) . "')";
				mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
				rmdir ($temp_dir);
			}
		}
		$sql = "UPDATE Galleries SET ModifiedDate = '" . date($MYSQL_DATE_FORMAT) . "' WHERE GalleryID=" . $_GET["gid"];
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
	}

	/*
	 * 
	 */
	function getFileExtension ($f) {
		$answer = "";
		$answer = explode (".", $f);
		$answer = $answer[count($answer)-1];
		return $answer;
	}



	/*
	 * Class definition for PictureGallery
	 */
	class PictureGallery {
		var $id = -1;
		var $name = "";
		var $description = "";
		var $createdDate = null;
		var $lastModified = null;
		var $isPublic = false;
		var $creator = ""; 

		function PictureGallery ($id, $n, $d, $cd, $lm, $p, $c) {
			$this->id = $id;
			$this->name = $n;
			$this->description = $d;
			$this->createdDate = $cd;
			$this->lastModified = $lm;
			$this->isPublic = $p;
			$this->creator = $c;
		}	
		
	}

	/*
	 * Class definition for PictureAlbum
	 */
	class PictureAlbum {
		var $id = -1;
		var $name = "";
		var $description = "";
		var $createdDate = null;
		var $lastModified = null;

		function PictureAlbum ($id, $n, $d, $cd, $lm) {
			$this->id = $id;
			$this->name = $n;
			$this->description = $d;
			$this->createdDate = $cd;
			$this->lastModified = $lm;
		}
	}

	/*
	 * Class definition for Album Items (Pictures)
	 */
	class Picture {
		var $id = -1;
		var $name = "";
		var $description = "";
		var $createdDate = null;
		var $lastModified = null;
		var $commentCount = 0;

		function Picture ($id, $n, $d, $cd, $lm) {
			$this->id = $id;
			$this->name = $n;
			$this->description = $d;
			$this->createdDate = $cd;
			$this->lastModified = $lm;
		}
	}




	/*
	 * 
	 */
	function getGallery ($gid) {
		$answer = null;
		$sql = "SELECT G.*, U.DisplayName FROM Galleries G, Users U WHERE G.CreatedBy=U.UserID AND G.GalleryID=" . $gid;
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		$row = mysql_fetch_array ($rs);
		$answer = new PictureGallery ($row["GalleryID"], $row["GalleryTitle"], $row["GalleryDescription"], $row["CreatedDate"], $row["ModifiedDate"], $row["IsPublic"], $row["CreatedBy"]);
		return $answer;
	}

	/*
	 * 
	 */
	function getPictures () {
		
	}

	/*
	 * 
	 */
	function getGalleries () {
		$answer = array ();
		$sql = "SELECT G.*, U.DisplayName FROM Galleries G, Users U WHERE G.MultiSite=1 AND G.CreatedBy=U.UserID ORDER BY GalleryTitle";
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		$i = 0;
		while ($row = mysql_fetch_array ($rs)) {
			$answer[$i] = new PictureGallery (
				$row["GalleryID"], $row["GalleryTitle"], $row["GalleryDescription"], $row["CreatedDate"], 
				$row["ModifiedDate"], $row["IsPublic"], $row["CreatedBy"]
			);
			$i++;
		}
		return $answer;
	}

	/*
	 * 
	 */
	function getAlbum ($aid) {
		$answer = null;
		$sql = "SELECT * FROM Albums WHERE AlbumID=" . $aid;
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		$row = mysql_fetch_array ($rs);
		$answer = new PictureAlbum ($row["AlbumID"], $row["AlbumTitle"], $row["AlbumDescription"], $row["CreatedDate"], $row["ModifiedDate"]);
		return $answer;
	}

	/*
	 * 
	 */
	function getAlbums ($gid) {
		$answer = array ();
		$sql = "SELECT * FROM Albums WHERE GalleryID=" . $gid;
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		$i = 0;
		while ($row = mysql_fetch_array ($rs)) {
			$answer[$i] = new PictureAlbum ($row["AlbumID"], $row["AlbumTitle"], $row["AlbumDescription"], $row["CreatedDate"], $row["ModifiedDate"]);
			$i++;
		}
		return $answer;
	}

?>



<?php
	
	/*
	 * View a full-size image and add comments
	 */
	if (isset ($_GET["iid"])) {
		$gallery = getGallery ($_GET["gid"]);
		$album = getAlbum ($_GET["aid"]);
		writeHeader (3, "Nerdery Pictures", 
			"<a class=\"boldWhiteLink\" href=\"galleries.php\">Picture Galleries</a> >> <a class=\"boldWhiteLink\" href=\"galleries.php?gid=" .
				$gallery->id . "\">" . $gallery->name . '</a> >> <a class="boldWhiteLink" href="galleries.php?gid=' . $_GET["gid"] . '&aid=' . $_GET["aid"] . 
				'">' . $album->name . '</a> >> Picture'
		);
		//$sql = "SELECT * FROM AlbumItems WHERE AlbumItemID=" . $_GET["iid"];
		$sql = "SELECT * FROM AlbumItems WHERE AlbumID=" . $_GET["aid"];
		$rs = mysql_query ($sql) or die (mysql_error() . "<br>" . $sql);
		$prev_id = -1;
		$found = false;
		$next_id = -1;
		for ($i=0; $i < mysql_num_rows($rs); $i++) {
			$row = mysql_fetch_array($rs);
			if ($found && $next_id < 0)
				$next_id = $row["AlbumItemID"];
			if ($row["AlbumItemID"] == $_GET["iid"]) {
				$found = true;
				echo '<br><img width="680" src="/resources/galleries/' . $_GET["aid"] . '/' . $row["FileName"] . '">';
				echo '<br><br><a href="galleries.php?gid=' . $_GET["gid"] . '&aid=' . $_GET["aid"] . '">Thumbnails</a><br><br>';
			}
			if (!$found)
				$prev_id = $row["AlbumItemID"];
		}
		if ($prev_id > 0)
			echo '<br><a href="galleries.php?gid=' . $_GET["gid"] . '&aid=' . $_GET["aid"] . '&iid=' . $prev_id . '">&lt;&lt;Previous</a>';
		if ($next_id > 0)
			echo '&nbsp;&nbsp;<a href="galleries.php?gid=' . $_GET["gid"] . '&aid=' . $_GET["aid"] . '&iid=' . $next_id . '">Next&gt;&gt;</a>';
		echo "<br><br>";

		writeSectionHeader ("Comments");
		echo '<table border="0" cellpadding="2" cellspacing="0" width="690">';
		$sql = "SELECT C.*, U.* FROM AlbumComments C, Users U WHERE C.CommentAuthor=U.UserID AND AlbumItemID=" . $_GET["iid"] . " ORDER BY CreatedDate DESC";
		$rs = mysql_query ($sql) or die (mysql_error() . "<br>" . $sql);
		if (mysql_num_rows ($rs) > 0) {
			while ($row = mysql_fetch_array ($rs)) {
				$class = (($class == "oddCell") ? "evenCell" : "oddCell");
				if ($row["JournalEntryDate"] > $_SESSION["PrevVisit"])
					$tclass = "boldGreenText";
				else
					$tclass = "boldBlueText";
				echo "<tr>" .
						"<td width=\"90\" class=\"$class\" align=\"center\" valign=\"top\">" .
							"<img width=\"50\" src=\"/images/signatures/" . $row["CommentAuthor"] . ".sig\">" .
							"<br><span class=\"smallBlackText\">" . $row["DisplayName"] . "</span>" .
							"<br><span class=\"smallBlueText\">" .
							date ("m/d/Y h:i a", strtotime ($row["CreatedDate"])) .
							"</span>" .
						"</td>" .
					 	"<td class=\"$class\" valign=\"top\">" .
							"<span class=\"smallBlackText\">" . str_replace ("\n\r", "<p>", $row["CommentBody"]) . "</span>" .
						"</td>" .
					 "</tr>";
				//echo '<tr><td>' . $row["CommentBody"] . '</td></tr>';
			}
		}
		else
			echo "<tr><td><br>Add a comment below...<br><br></tr></td>";
		echo '</table>';

		writeSectionHeader ("Add a comment");
		$frm = new Form ("field", "addComment", 'galleries.php?gid=' . $_GET["gid"] . '&aid=' . $_GET["aid"] . 
			'&iid=' . $_GET["iid"], "addComment");
		$frm->addHidden ("gid", $_GET["gid"]);
		$frm->addHidden ("aid", $_GET["aid"]);
		$frm->addHidden ("iid", $_GET["iid"]);
		$frm->addFormElement ("comment_text", "textarea", "Comment", "", true);
		$frm->method = "post";
		$frm->draw();
	}
	/*
	 * Thumbnails for an album
	 */
	else if (isset ($_GET["aid"])) {
		$gallery = getGallery ($_GET["gid"]);
		$album = getAlbum ($_GET["aid"]);
		writeHeader (3, "Nerdery Pictures", 
			"<a class=\"boldWhiteLink\" href=\"galleries.php\">Picture Galleries</a> >> <a class=\"boldWhiteLink\" href=\"galleries.php?gid=" .
				$gallery->id . "\">" . $gallery->name . "</a> >> " . $album->name
		);

		echo '<table border="0" cellpadding="4" cellspacing="0" width="700">';
		//echo '<tr>';
		$sql = "SELECT I.*, COUNT(C.CommentID) CommentCount FROM AlbumItems I LEFT OUTER JOIN AlbumComments C ON I.AlbumItemID=C.AlbumItemID " .
			"WHERE I.AlbumID=" . $_GET["aid"] . " GROUP BY I.AlbumItemID";
		$rs = mysql_query ($sql) or die (mysql_error() . "<br>" . $sql);
		$first = true;
		$count = 0;
		while ($row = mysql_fetch_array ($rs)) {
			if ($count == 6) {
				if (!$first)
					echo "</tr>";
				echo "<tr>";
				$count = 0;
				$first = false;
			}
			if ($row["ModifiedDate"] > $_SESSION["PrevVisit"])
				$classname = "smallModified";
			else
				$classname = "smallUnmodified";
			echo '<td valign="top" align="center"><a href="galleries.php?gid=' . $_GET["gid"] . '&aid=' . $_GET["aid"] . 
				'&iid=' . $row["AlbumItemID"] . '"><img border="0" src="/resources/galleries/' . 
				$row["AlbumID"] . '/t_' . $row["FileName"] . '"></a><br><span class="' . $classname . '">' .
				$row["CommentCount"] . ' comment';
			if ($row["CommentCount"] == 1)
				echo '</span></td>';
			else
				echo 's</span></td>';
			$count++;
		}
		echo '</tr>';
		echo '</table><br>';
		// form for adding new images to this album
		if ($gallery->isPublic || $gallery->creator == $_SESSION["UserID"]) {
			writeSectionHeader ("Add Images");
			$frm = new Form ("field", "addImages", 'galleries.php?gid=' . $_GET["gid"] . '&aid=' . $_GET["aid"], "addImages");
			$frm->addDescription ("You may upload multiple images in a zip file, or a single jpg or gif file");
			$frm->addFormElement ("album_files", "file", "File", "", true);
			$frm->method = "post";
			$frm->draw();
		}
		
		// form for adding a picture
	}
	/*
	 * Write out a list of albums in a gallery
	 */
	else if (isset ($_GET["gid"])) {
		$gallery = getGallery ($_GET["gid"]);
		writeHeader (3, "Nerdery Pictures", "<a class=\"boldWhiteLink\" href=\"galleries.php\">Picture Galleries</a> >> " . $gallery->name);

		echo '<table border="0" cellpadding="4" cellspacing="0" width="700">';
		$each = getAlbums ($_GET["gid"]);
		$i = 0;
		echo
			"<tr>" .
				"<td colspan=\"2\">" .
					'<a class="modifiedLink" href="galleries.php?gid=' . $_GET["gid"] . '">' .
						$gallery->name .
					'</a>' .
				"</td>" .
			"</tr>";
		while ($a = $each[$i]) {
			if ($a->lastModified > $_SESSION["PrevVisit"])
				$classname = "modifiedLink";
			else
				$classname = "unmodifiedLink";
			echo 
				"<tr>" .
					"<td width=\"40\"></td>" .
					"<td width=\"660\">" .
						"<a class=\"" . $classname . "\" href=\"galleries.php?gid=" . $_GET["gid"] . "&aid=" . $a->id . "\">" . $a->name . "</a>" .
						"<br><span class=\"itemText\">" .
							$a->description . 
						"</span>" .
					"</td>" .
				"</tr>";
			$i++;	
		}
		echo '</table><br>';
		
		if ($gallery->isPublic || $gallery->creator == $_SESSION["UserID"]) {
			// form for creating an album
			writeSectionHeader ("Create an Album");
			$frm = new Form ("field", "createAlbum", "galleries.php?gid=" . $_GET["gid"], "addAlbum");
			$frm->addHidden ("gid", $_GET["gid"]);
			$frm->addFormElement ("album_name", "text", "Name", "", true);
			$frm->addFormElement ("album_description", "textarea", "Description", "", true);
			$frm->addFormElement ("album_files", "file", "Zip File", "", true);
			$frm->method = "post";
			$frm->draw();
		}
	}
	/*
	 * DEFAULT:  write out a list of galleries
	 */
	else {
		writeHeader (3, "Nerdery Pictures", "Picture Galleries");
		echo '<table border="0" cellpadding="2" cellspacing="0" width="700">';
		$each = getGalleries ();
		$i = 0;
		while ($g = $each[$i]) {
			//print_r ($g);
			if ($g->lastModified > $_SESSION["PrevVisit"])
				$classname = "modifiedLink";
			else
				$classname = "unmodifiedLink";
			echo "<tr><td colspan=\"2\"><a href=\"galleries.php?gid=" . $g->id . "\"><span class=\"" . $classname . "\">" . 
				$g->name . "</span></a></td></tr><tr><td width=\"20\"></td><td width=\"680\"><span class=\"itemText\">" . 
				$g->description . "</span></td></tr>";
			$i++;	
		}
		echo '</table><br>';

		writeSectionHeader ("Create a Gallery");
		$frm = new Form ("field", "createGallery", "galleries.php", "addGallery");
		$frm->addDescription ("All users can add pictures to public galleries.");
		$frm->addFormElement ("is_public", "checkbox", "Public", true, true);
		$frm->addFormElement ("gallery_name", "text", "Name", "", true);
		$frm->addFormElement ("gallery_description", "textarea", "Description", "", true);
		$frm->method = "post";
		$frm->draw();
	}

	writeFooter ();
?>