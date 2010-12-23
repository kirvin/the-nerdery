<?php
	ob_start ();

	require ('includes/global_functions.php');
	require ('includes/framework_functions.php');
	//require ('includes/form_functions.php');
	require ('includes/news_functions.php');

	session_set_save_handler ("open_session", "close_session", "read_session", "write_session", 
		"destroy_session", "session_gc");
	session_start ();

	if ($_SESSION["ValidLogin"] != 1)
		header ("location: login.php?r=" . $_SERVER["PHP_SELF"]);
		
	if (isset($_GET["j"]))
		$journal_id = $_GET["j"];
	else if (isset($_POST["j"]))
		$journal_id = $_POST["j"];
	else
		header ("location: news.php");
	if (isset($_GET["e"]))
		$item_id = $_GET["e"];
	else if (isset($_POST["e"]))
		$item_id = $_POST["e"];
	else
		header ("location: news.php?j=" . $journal_id);

	//-------------------------------------------------------------------------
	// Insert a new comment about this news item
	//-------------------------------------------------------------------------
	if ($page_action == "insertComment") {
		$sql = "INSERT INTO JournalEntryComments (JournalEntryID, CreatedDate, CommentAuthor, CommentText) " .
			"VALUES (" . $item_id . ",'" . date ("Y-m-d H:i:s") . "','" . $_SESSION["UserID"] . "','" . $_POST["comment_text"] . "')";
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
		$sql = "UPDATE JournalEntries SET LastModified='" . date ("Y-m-d H:i:s") . "' WHERE JournalEntryID=" . $item_id;
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
		$sql = "UPDATE Journals SET LastModified='" . date ("Y-m-d H:i:s") . "' WHERE JournalID=" . $journal_id;
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
	}
		

	WriteHeader (5, "The Nerdery::News"); 
	
	$sql = "SELECT * FROM Journals WHERE JournalID=$journal_id";
	$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
	$row = mysql_fetch_array ($rs);
?>

<table border="0" width="750" cellpadding="0" cellspacing="0">
	<tr>
		<td width="25" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td>
						<span class="boldWhiteMediumText">
							<a href="news.php" class="boldWhiteLink">News Columns</a>
							&nbsp;&gt;&gt;&nbsp;
							<a href="view_news.php?j=<?php echo $journal_id;?>" class="boldWhiteLink">
								<?php echo $row["JournalTitle"]; ?>
							</a>
							&nbsp;&gt;&gt;&nbsp;
							News Item
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
			writeNewsItem ($item_id, $JOURNALENTRIES_DIR);
		?>
		</td>
		<td></td>
	</tr>
	<tr><td colspan="3"><br></td></tr>
	
	<!-- comments for this news item -->
	<tr>
		<td width="25" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<span class="boldWhiteMediumText">
				<a href="news.php" class="boldWhiteLink">News Columns</a>
				&nbsp;&gt;&gt;&nbsp;
				<a href="view_news.php?j=<?php echo $journal_id;?>" class="boldWhiteLink">
					<?php echo $row["JournalTitle"]; ?>
				</a>
				&nbsp;&gt;&gt;&nbsp;
				Comments
			</span>
		</td>
		<td width="25"><img border="0" height="32" width="25" src="images/section.header.e.gif"></td>
	</tr>
	<tr>
		<td></td>
		<td align="center">
			<?php
				writeJournalEntryComments ($item_id);
			?>
		</td>
		<td></td>
	</tr>
	<tr><td colspan="3"><br></td></tr>

	<!-- form for adding a new comment -->
	<tr>
		<td width="25" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<span class="boldWhiteMediumText">
				Add Your Comments
			</span>
		</td>
		<td width="25"><img border="0" height="32" width="25" src="images/section.header.e.gif"></td>
	</tr>
	<tr>
		<td align="center" colspan="3">
			<?php
				$fstruct = array (
					array ("paction", "", "hidden", "insertComment", false),
					array ("j", "", "hidden", $journal_id, false),
					array ("e", "", "hidden", $item_id, false),
					array ("comment_text", "Comments", "textarea", "", true),
				);
				createForm ($fstruct, "field", "addList", "view_news_item.php", true, "Add my $0.02", "");
			?>
		</td>
	</tr>
</table>


<?php
	WriteFooter ();
?>
