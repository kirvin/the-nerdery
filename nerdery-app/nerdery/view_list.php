<?php
	ob_start ();

	require ('includes/global_functions.php');
	require ('includes/framework_functions.php');
	//require ('includes/form_functions.php');
	require ('includes/list.functions.php');

	session_set_save_handler ("open_session", "close_session", "read_session", "write_session", 
		"destroy_session", "session_gc");
	session_start ();

	if ($_SESSION["ValidLogin"] != 1)
		header ("location: login.php?r=" . $_SERVER["PHP_SELF"]);
	if (isset ($_POST["list_id"]))
		$list_id = $_POST["list_id"];
	else if (isset ($_GET["l"]))
		$list_id = $_GET["l"];
	else
		header ("location: lists.php");
?>

<?php 
	if ($page_action == "insertListItem") {
		/*
		$sql = "SELECT COUNT(ListItemID) AS ItemCount FROM ListItems WHERE ListID=" . $list_id;
		$rs = mysql_query ($sql);
		$row = mysql_fetch_array ($rs);
		$new_cnt = $row[0];
		*/
		echo ("type: " . $_FILES["item_file"]["type"]);
		if ($_FILES["item_file"]["type"] == "audio/mpeg")
			$extension = "mp3";
		else if ($_FILES["item_file"]["type"] == "image/pjpeg")
			$extension = "jpg";
		else if ($_FILES["item_file"]["type"] == "application/x-shockwave-flash")
			$extension = "swf";
		else if ($_FILES["item_file"]["type"] == "video/avi")
			$extension = "avi";
		else if ($_FILES["item_file"]["type"] == "video/mpeg")
			$extension = "mpeg";
		else
			$extension = "";
		$sql = "INSERT INTO ListItems (ListID, ListItemOwner, CreatedDate, LastModified, ListItemFile, ListItemText, ListItemURL) " .
			"VALUES (" . $list_id . ",'" . $_SESSION["UserID"] . "','" . date ("Y-m-d H:i:s") . "','" . date ("Y-m-d H:i:s") . 
			"','" . $extension . "','" . $_POST["item_text"] . "','" . $_POST["item_url"] . "')";
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		// get id of newly inserted record
		$sql = "SELECT LAST_INSERT_ID() AS NewID";
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		$row = mysql_fetch_array ($rs);
		$new_cnt = $row["NewID"];
		
		// upload file if necessary
		if ($_FILES["item_file"]["name"]) {
			$upload_path = realpath (".") . "\\misc\\listitems\\" . $list_id . "\\";
			if (!file_exists ($upload_path))
				mkdir ($upload_path);
			$upload_path = $upload_path . $new_cnt . "." . $extension;
			//echo "UPLOAD TO: " . $upload_path;
			move_uploaded_file ($_FILES["item_file"]["tmp_name"], $upload_path); 
		}
		$sql = "UPDATE Lists SET LastModified='" . date ("Y-m-d H:i:s") . "' WHERE ListID=" . $list_id;
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
	}


	WriteHeader (2, "The Nerdery::View List"); 


	$curr_list = new VotingList ($list_id);
?>

<script language="javascript" type="text/javascript">
</script>

<form name="redirectForm" action="view_list.php" method="post">
	<input type="hidden" name="paction" value="">
	<input type="hidden" name="list_id" value="">
</form>

<table border="0" width="750" cellpadding="0" cellspacing="0">
	<tr>
		<td width="25" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td>
						<span class="boldWhiteMediumText">
							<a href="lists.php" class="boldWhiteLink">All lists</a>&nbsp;&gt;&gt;&nbsp;<?php echo $curr_list->listTitle; ?>
						</span>
					</td>
					<td align="right">
					</td>
				</tr>
			</table>
		</td>
		<td width="25"><img border="0" height="32" width="25" src="images/section.header.e.gif"></td>
	</tr>
	<tr>
		<td></td>
		<td align="center">
		<?php
			writeListItems ($curr_list);
		?>
		</td>
		<td></td>
	</tr>
	<form name="listForm" action="view_discussion.php" method="post">
	<tr>
		<td></td>
		<td align="center">
			<input type="submit" value="&lt;&lt;Back" onclick="document.listForm.action='lists.php';">
		</td>
		<td></td>
	</tr>
	</form>

	<tr>
		<td width="25" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<span class="boldWhiteMediumText">
				<a href="lists.php" class="boldWhiteLink">All lists</a>
				&nbsp;&gt;&gt;&nbsp;<?php echo $curr_list->listTitle; ?>
				&nbsp;&gt;&gt;&nbsp;Add Item
			</span>
		</td>
		<td width="25"><img border="0" height="32" width="25" src="images/section.header.e.gif"></td>
	</tr>
	<tr>
		<td align="center" colspan="3">
			<?php
				$fstruct = array (
					array ("paction", "", "hidden", "insertListItem", false),
					array ("list_id", "", "hidden", $list_id, false),
					array ("item_text", "Text", "textarea", $item_text_in, true),
					array ("item_url", "URL", "text", $item_url_in, false),
					array ("item_file", "File", "file", $item_file_in, false)
				);
				createForm ($fstruct, "field", "addList", "view_list.php", true, "Add Item", "");
			?>
		</td>
	</tr>
	<tr><td colspan="3"><br></td></tr>

</table>


<?php
	WriteFooter ();
?>
