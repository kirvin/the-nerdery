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

	if (isset ($_POST["discussion_id"]))
		$discussion_id = $_POST["discussion_id"];
	else if (isset ($_GET["d"]))
		$discussion_id = $_GET["d"];

	if (isset ($_POST["thread_id"]))
		$thread_id = $_POST["thread_id"];
	else if (isset ($_GET["t"]))
		$thread_id = $_GET["t"];

	if (isset ($_POST["comment_id"]))
		$comment_id = $_POST["comment_id"];
	else if (isset ($_GET["c"]))
		$comment_id = $_GET["c"];

	if (isset ($_POST["page"]))
		$curr_page = $_POST["page"];
	else
		$curr_page = 1;

	//----------------------------------------------------------------------
	// Insert a reply into a thread
	//----------------------------------------------------------------------
	if ($_POST["paction"] == "insertReply") {
		$old_thread = new DiscussionThread ($_POST["discussion_id"], $_POST["thread_id"], true);
		$thread_end = ceil ($old_thread->threadID);
		$thread_end = (($thread_end == floor ($old_thread->threadID)) ? $thread_end+1 : $thread_end);
		//echo "thread_end: " . $thread_end . "<br>";
		$idx = 0;
		while ($old_thread->threadComments[$idx]->commentID != $_POST["comment_id"] && $idx < count($old_thread->threadComments))
			$idx++;
		$parent = $old_thread->threadComments[$idx];
		$prev_comment = null;
		$next_parent_sibling = null;
		//$youngest_sibling = null;
		//$youngest_nephew = null;
		$after = -1;
		$idx++;
		//echo $idx . "::" . $parent->commentLevel . "::" . $parent->commentID . "<br>";
		if ($idx != count($old_thread->threadComments)) {
			$done = false;
			while (!$done) {
				//echo $idx . "::" . $old_thread->threadComments[$idx]->commentLevel . "::" . $old_thread->threadComments[$idx]->commentID . "<br>";
				if ($old_thread->threadComments[$idx]->commentLevel == $parent->commentLevel
						&& $old_thread->threadComments[$idx]->commentID > $parent->commentID) {
					$next_parent_sibling = $old_thread->threadComments[$idx];
					$prev_comment = $old_thread->threadComments[$idx-1];
					$done = true;
				}
				/*
				else if ($youngest_sibling !== null && $old_thread->threadComments[$idx]->commentLevel > $youngest_sibling->commentLevel) {
					$youngest_nephew = $old_thread->threadComments[$idx];
				}
				else if ($old_thread->threadComments[$idx]->commentLevel >= $parent->commentLevel) {
					$done = true;
				}
				*/
				$idx++;
				if ($idx == count($old_thread->threadComments))
					$done = true;
			}
			//$after = $old_thread->threadComments[$idx+1];
		}
		else {
			$after = null;
		}
		if ($after == null) {
			$last_comment = $old_thread->threadComments[count($old_thread->threadComments)-1];
			//echo "parent: " . $parent->commentID . "<br>thread_end: " . $thread_end . "<br>last_comment: " . $last_comment->commentID . "<br>";
			$nid = $last_comment->commentID + (($thread_end - $last_comment->commentID) / 10);
			//$insert_sql = "INSERT INTO DiscussionComments (DiscussionID, CommentID, CommentSubject, CommentAuthor, CommentDate, CommentText, CommentLevel) " .
			//	"VALUES (" . $_POST["discussion_id"] .",?,'')";
			$sql = "INSERT INTO DiscussionComments (DiscussionID, CommentID, CommentSubject, CommentAuthor, CommentDate, CommentText, CommentLevel) " .
				"VALUES (" . $parent->discussionID . "," . $nid . ",'" . $_POST["message_subject"] . "','" . 
				$_SESSION["user"]->userID . "','" . date("Y-m-d H:i:s") . "','" . $_POST["message_text"] . "'," . ($parent->commentLevel+1) . ")";
			mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		}
		else {
			//echo "next_parent_sibling: " . $next_parent_sibling->commentID . "<br>prev_comment: " . $prev_comment->commentID . "<br>";
			if ($next_parent_sibling == null && $prev_comment == null) {
				$prev_comment = $old_thread->threadComments[count($old_thread->threadComments)-1];
				$nid = $prev_comment->commentID + (($thread_end - $prev_comment->commentID) / 10);
				$sql = "INSERT INTO DiscussionComments (DiscussionID, CommentID, CommentSubject, CommentAuthor, CommentDate, CommentText, CommentLevel) " .
					"VALUES (" . $parent->discussionID . "," . $nid . ",'" . $_POST["message_subject"] . "','" . 
					$_SESSION["user"]->userID . "','" . date("Y-m-d H:i:s") . "','" . $_POST["message_text"] . "'," . ($parent->commentLevel+1) . ")";
				mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
			}
			else if ($next_parent_sibling !== null && $prev_comment !== null) {
				$nid = $prev_comment->commentID + (($next_parent_sibling->commentID - $prev_comment->commentID) / 10);
				$sql = "INSERT INTO DiscussionComments (DiscussionID, CommentID, CommentSubject, CommentAuthor, CommentDate, CommentText, CommentLevel) " .
					"VALUES (" . $parent->discussionID . "," . $nid . ",'" . $_POST["message_subject"] . "','" . 
					$_SESSION["user"]->userID . "','" . date("Y-m-d H:i:s") . "','" . $_POST["message_text"] . "'," . ($parent->commentLevel+1) . ")";
				mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
			}
		}
		$sql = "UPDATE Discussions SET LastModified='" . date("Y-m-d H:i:s") . "' WHERE DiscussionID=" . $parent->discussionID;
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);

		// insert a record into the history table
		$sql = "INSERT INTO NerderyEvents (EventTitle, EventDescription, UserID, EventTypeID, EventURL) " .
			"VALUES ('" . $_SESSION["user"]->displayName . " added \$0.02 or less to a discussion', CONCAT('" . 
			$_SESSION["user"]->displayName . " posted a comment in the discussion \"', " .
			"(SELECT DiscussionTitle FROM Discussions WHERE DiscussionID=" . $discussion_id . "), '\"'), " .
			"'" . $_SESSION["UserID"] . "', " .
			"(SELECT EventTypeID FROM NerderyEventType WHERE EventTypeName='DiscussionComment'), " .
			"'/view_thread.php?t=" . $nid . "&p=1&d=" . $discussion_id . "')";
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);

		/*
		echo "insert reply between " . $parent->commentID . " and " . $after->commentID . "<br>";
		
		$sql = "SELECT MIN(CommentID) AS NextSibling FROM DiscussionComments WHERE DiscussionID=" . 
			$_POST["discussion_id"] . " AND CommentLevel=" . $parent->commentLevel . " AND CommentID > " .
			$parent->commentID . " GROUP BY DiscussionID";
		echo $sql . "<br>";
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error());
		$row = mysql_fetch_array ($rs);
		if ($row["NextSibling"] < 1) {
			echo "[1]nothing found as parent's next sibling, add at end<br>";
			// get the id of the last comment in the discussion here (should be the last comment in this thread)
			// and find some comment id between the last comment id and the next whole number
			$sql = "SELECT MAX(CommentID) AS LastComment FROM DiscussionComments WHERE DiscussionID=" . 
				$_POST["discussion_id"] . " AND CommentID > " . $parent->commentID . " AND CommentLevel > " . 
				$parent->commentLevel;
			$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error());
			$row = mysql_fetch_array ($rs);
			$prior_id = $row["LastComment"];
			$next_id = ceil ($prior_id);
		}
		else {
			$after_cid = $row["NextSibling"];
			if (floor($after_cid) != floor($parent)) {
				echo "[2]nothing found as parent's next sibling, add at end<br>";
				$sql = "SELECT MAX(CommentID) AS LastComment WHERE CommentID < " . ceil($parent);
				echo $sql . "<br>";
				// get the id of the last comment with the same floor as the $parent (Max comment id where 
				// comment id < ceiling ($parent)) and find some comment id between the last comment id and 
				// the next whole number
			}
			else {
				//echo (floor($_POST["comment_id"])+1);
				$sql = "SELECT MAX(CommentID) AS OldestChild FROM DiscussionComments WHERE DiscussionID=" .
					$_POST["discussion_id"] . " AND CommentLevel > " . $parent->commentLevel . " AND CommentID < " .
					$after_cid;
				echo $sql . "<br>";
				$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error());
				if (mysql_num_rows($rs) > 0) {
					$oc = mysql_fetch_array ($rs);
					echo "add between " . $oc["OldestChild"] . " and " . $after_cid;
					
				}
				else {
					echo "nothing found as oldest child, add between parent and parent's next sibling.<br>";
					// get some new id in between parent's id and id of parent's next sibling
				}
			}
		}
		echo "prior_id: " . $prior_id . ", next_id " . $next_id;
		$insert_sql = "INSERT INTO DiscussionComments (DiscussionID, CommentID, CommentSubject, CommentAuthor, CommentDate, CommentText, CommentLevel) " .
			"VALUES (" . $_POST["discussion_id"] .",?,'')";
		*/


		/*				
		$sql = "SELECT MAX (CommentID) AS LastChild FROM DiscussionComments WHERE DiscussionID=" . $_POST["discussion_id"] .
			" AND CommentLevel > " . $before->commentLevel . " AND CommentID > " . $before->commentID . 
			" AND CommentID < ";
		*/
	}

	WriteHeader (4, "The Nerdery::View Discussion Thread"); 
	writeCP ();
?>

<script language="javascript" type="text/javascript">
	function viewDiscussionPage (pid) {
		document.redirectForm.page.value = pid;
		document.redirectForm.submit ();
	}
	
	function viewDiscussion (did, p) {
		document.redirectForm.action = "view_discussion.php";
		document.redirectForm.discussion_id.value = did;
		document.redirectForm.page.value = p;
		document.redirectForm.submit ();
	}

	function viewThread (tid) {
		document.redirectForm.action = "view_thread.php";
		document.redirectForm.thread_id.value = tid;
		document.redirectForm.page.value = 1;
		document.redirectForm.submit ();
	}
	
	function replyToComment (cid) {
		document.redirectForm.action = "view_thread.php";
		document.redirectForm.paction.value = "composeReply";
		document.redirectForm.comment_id.value = cid;
		document.redirectForm.submit ();	
	}
</script>

<?php
	//$curr_discussion = new Discussion ($_POST["discussion_id"], true);
	$curr_thread = new DiscussionThread ($discussion_id, $thread_id, true);
	$pages = ceil (count($curr_discussion->discussionComments) / $MAX_PAGE_COMMENTS);
?>

<form name="redirectForm" action="view_thread.php" method="post">
	<input type="hidden" name="paction" value="">
	<input type="hidden" name="page" value="">
	<input type="hidden" name="discussion_id" value="<?php echo $curr_thread->discussionID; ?>">
	<input type="hidden" name="thread_id" value="<?php echo $curr_thread->threadID; ?>">
	<input type="hidden" name="comment_id" value="">
</form>

<?php
	if ($_POST["paction"] == "composeReply") {
?>
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
								$target_comment = $curr_thread->getDiscussionComment($comment_id);
								$msubject = $target_comment->commentSubject;
								if (substr ($msubject, 0, 3) != "Re:") {
									$msubject = "Re: " . $msubject;
								}
								$mbody = $target_comment->commentText;
								if (substr ($mbody, 0, 3) != "<i>")
									$mbody = "<i>" . $mbody . "</i>";

								$fstruct = array (
									array ("paction", "", "hidden", "insertReply", false),
									array ("discussion_id", "", "hidden", $curr_thread->discussionID, false),
									array ("thread_id", "", "hidden", $curr_thread->threadID, false),
									array ("comment_id", "", "hidden", $comment_id, false),
									array ("message_subject", "Subject", "text", $msubject, true),
									array ("message_text", "Message", "textarea", $mbody, true)
								);
								createForm ($fstruct, "field", "addReply", "view_thread.php", true, "Add Message", "");
							?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br>
<?php		
	}
?>

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
							echo "<a class=\"boldWhiteLink\" href=\"javascript: viewDiscussion(" . 
								$curr_thread->discussionID . "," . $curr_page . ");\">";
							if (strlen($curr_thread->discussionTitle) > 15)
								echo substr ($curr_thread->discussionTitle, 0, 15) . "...";
							else
								echo $curr_thread->discussionTitle;
							echo "</a>&nbsp;&gt;&gt;&nbsp;";
							if (strlen ($curr_thread->threadTitle) > 15)
								echo substr ($curr_thread->threadTitle, 0, 15) . "...";
							else
								echo $curr_thread->threadTitle;
						?>
						</span>
					</td>
					<td align="right">
						<span class="boldWhiteMediumText">
						<?php
							for ($pindex = 1; $pindex <= $pages; $pindex++) {
								if ($pindex != $curr_page)
									echo "&nbsp;<a href=\"javascript: viewDiscussionPage($pindex);\" class=\"boldWhiteLink\">$pindex</a>&nbsp;";
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
			writeThread ($curr_thread, $thread_id, $application);
		?>
		<br>
		</td>
		<td></td>
	</tr>
</table>

<?php
	WriteFooter ();

	$application->save();
?>
