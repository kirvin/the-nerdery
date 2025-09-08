<?php
require_once ("includes/nerdery/net.theirvins.nerdery.Application.php");
require_once ("includes/global_functions.php");
require_once ('includes/cp.php');
require_once ("includes/framework_functions.php");
require_once ('includes/form_functions.php');
ob_start();

/*
 * Set up session handling
 */
session_set_save_handler ("open_session", "close_session", "read_session", "write_session",
	"destroy_session", "session_gc");
session_start ();

/*
 * get a reference to the application object
 */
$application = &Application::getInstance();

/*
 * Check for login
 */
if (strcmp($_SERVER["PHP_SELF"], '/login.php') != 0) {
	if ($_SESSION["ValidLogin"] != 1)
		header ("location: login.php?r=" . $_SERVER["PHP_SELF"]);
}

require_once ('includes/discussion.functions.php');

	$errMsg = "";

	if (isset ($_POST["page"]))
		$curr_page = $_POST["page"];
	else if (isset ($_GET["page"]))
		$curr_page = $_GET["page"];
	else if (isset ($_GET["p"]))
		$curr_page = $_GET["p"];
	else
		$curr_page = 1;
	if (isset ($_POST["discussion_id"]))
		$discussion_id = $_POST["discussion_id"];
	else if (isset ($_GET["discussion_id"]))
		$discussion_id = $_GET["discussion_id"];
	else if (isset ($_GET["d"]))
		$discussion_id = $_GET["d"];
	else
		$discussion_id = -1;

	//----------------------------------------------------------------------
	// Create a new discussion
	//----------------------------------------------------------------------
	if ($page_action == "insertDiscussion") {
		$sql = "INSERT INTO Discussions (DiscussionTitle, CreatedDate, LastModified, DiscussionDescription) VALUES ('" .
			mysql_escape_string($_POST["discussion_title"]) . "','" . date("Y-m-d H:i:s") . "','" . date("Y-m-d H:i:s") . "','" .
			mysql_escape_string($_POST["discussion_description"]) . "')";
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
		$discussion_id = mysql_insert_id ();

		// insert a record into the history table
		$sql = "INSERT INTO NerderyEvents (EventTitle, EventDescription, UserID, EventTypeID, EventURL) " .
			"VALUES ('" . $_SESSION["user"]->displayName . " created a new discussion', CONCAT('" .
			$_SESSION["user"]->displayName . " created the discussion \"', " .
			"(SELECT DiscussionTitle FROM Discussions WHERE DiscussionID=" . $discussion_id . "), '\"'), " .
			"'" . $_SESSION["UserID"] . "', " .
			"(SELECT EventTypeID FROM NerderyEventType WHERE EventTypeName='Discussion'), " .
			"'/view_discussion.php?discussion_id=" . $discussion_id . "')";
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
	}
	/*
	 * Insert a new thread into a discussion
	 */
	else if ($page_action == "insertMessage") {
		$sql = "SELECT COUNT(CommentID) AS CommentCount FROM DiscussionComments WHERE DiscussionID=" . $discussion_id . " AND CommentLevel=1";
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
		$row = mysql_fetch_array ($rs);
		$nid = $row["CommentCount"];
		if ($nid != -1) {
			$sql = "INSERT INTO DiscussionComments (DiscussionID, CommentID, CommentSubject, CommentAuthor, CommentDate, " .
				"CommentText, CommentLevel) VALUES (" . $discussion_id . "," . ($nid + 1) . ",'" . mysql_escape_string($_POST["message_subject"]) . "','" .
				$_SESSION["user"]->userID . "','" . date("Y-m-d H:i:s") . "','" . mysql_escape_string($_POST["message_text"]) . "',1)";
			mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
			$sql = "UPDATE Discussions SET LastModified='" . date("Y-m-d H:i:s") . "' WHERE DiscussionID=" . $discussion_id;
			mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");

			// insert a record into the history table
			$sql = "INSERT INTO NerderyEvents (EventTitle, EventDescription, UserID, EventTypeID, EventURL) " .
				"VALUES ('" . $_SESSION["user"]->displayName . " created a new discussion topic', CONCAT('" .
				$_SESSION["user"]->displayName . " started a new discussion topic in \"', " .
				"(SELECT DiscussionTitle FROM Discussions WHERE DiscussionID=" . $discussion_id . "), '\"'), " .
				"'" . $_SESSION["UserID"] . "', " .
				"(SELECT EventTypeID FROM NerderyEventType WHERE EventTypeName='DiscussionComment'), " .
				"'/view_discussion.php?d=" . $discussion_id . "')";
			mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		}
	}

	WriteHeader (4, "The Nerdery::View Discussion");
	writeCP ();
?>

<script language="javascript" type="text/javascript">
	function viewDiscussionPage (pid) {
		document.redirectForm.page.value = pid;
		document.redirectForm.submit ();
	}

	function viewThread (tid, p) {
		document.redirectForm.action = "view_thread.php";
		document.redirectForm.thread_id.value = tid;
		document.redirectForm.page.value = p;
		document.redirectForm.submit ();
	}
</script>

<?php
	$curr_discussion = new Discussion ($discussion_id, true);
	$pages = ceil (count($curr_discussion->discussionComments) / $MAX_PAGE_COMMENTS);
?>

<form name="redirectForm" action="view_discussion.php" method="post">
	<input type="hidden" name="paction" value="">
	<input type="hidden" name="page" value="">
	<input type="hidden" name="discussion_id" value="<?php echo $curr_discussion->discussionID; ?>">
	<input type="hidden" name="thread_id" value="">
</form>

<table border="0" width="750" cellpadding="0" cellspacing="0">
	<tr>
		<td width="25" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<table border="0" width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<span class="boldWhiteMediumText">
						<?php
							echo "<a class=\"boldWhiteLink\" href=\"discuss.php\">Discussions</a>&nbsp;&gt;&gt;&nbsp;";
							echo $curr_discussion->discussionTitle;
						?>
						</span>
					</td>
					<td align="right">
						<span class="boldWhiteMediumText">
						<?php
							for ($pindex = 1; $pindex <= $pages; $pindex++) {
								if ($pindex != $curr_page)
									echo "&nbsp;<a href=\"view_discussion.php?d=" . $curr_discussion->discussionID . "&page=" . $pindex . "\" class=\"boldWhiteLink\">$pindex</a>&nbsp;";
								else
									echo "&nbsp;" . $curr_page . "&nbsp;";
							}
						?>
						</span>
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
			writeDiscussionThreads ($curr_discussion, ($curr_page-1) * $MAX_PAGE_COMMENTS, $curr_page);
		?>
		</td>
		<td></td>
	</tr>
	<tr>
		<td colspan="3" align="center" style="border-style: solid; border-color: #336600; border-width: 1px; border-left: none;border-right: none;">
			<span class="boldGreenText">
			Page:&nbsp;
			<?php
				for ($pindex = 1; $pindex <= $pages; $pindex++) {
					if ($pindex != $curr_page)
						echo "&nbsp;<a href=\"view_discussion.php?d=" . $curr_discussion->discussionID . "&page=" . $pindex . "\" class=\"boldGreenLink\">$pindex</a>&nbsp;";
					else
						echo "&nbsp;" . $curr_page . "&nbsp;";
				}
			?>
			</span>
		</td>
	</tr>
</table>

<br>

<table border="0" width="750" cellpadding="0" cellspacing="0">
	<tr>
		<td width="25"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<span class="boldWhiteMediumText">
				Add a message
			</span>
		</td>
		<td width="25" align="right"><img border="0" height="32" width="25" src="images/section.header.e.gif"></td>
	</tr>
	<tr>
		<td colspan="3" align="center">
			<table border="0" cellpadding="0" cellspacing="0" width="700">
			<?php if (strlen($errMsg) > 0) { ?>
				<tr>
					<td><?php echo $errMsg ?></td>
				</tr>
			<?php } ?>
				<tr>
					<td align="center">
						<?php
							$fstruct = array (
								array ("paction", "", "hidden", "insertMessage", false),
								array ("discussion_id", "", "hidden", $discussion_id, false),
								array ("page", "", "hidden", $curr_page, false),
								array ("message_subject", "Subject", "text", $ltitle_in, true),
								array ("message_text", "Body", "textarea", $ldesc_in, true)
							);
							createForm ($fstruct, "field", "addDiscussion", "view_discussion.php", true, "Add Message", "");
						?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>


<?php
	WriteFooter ();

	$application->save();
?>
