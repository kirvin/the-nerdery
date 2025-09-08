<?php
require_once ("includes/nerdery/net.theirvins.nerdery.Application.php");
require_once ("includes/global_functions.php");
require_once ('includes/cp.php');
require_once ("includes/framework_functions.php");
require_once ('includes/form_functions.php');
require_once("includes/nerdery/net.theirvins.nerdery.services.ListService.php");
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

// get a reference to the list service
$listService = ListService::getInstance();

// Check for login
checkSession();

require ('includes/list.functions.php');

	if (isset ($_POST["l"]))
		$list_id = $_POST["l"];
	else if (isset ($_GET["l"]))
		$list_id = $_GET["l"];
	else
		header ("location: lists.php");
	if (isset ($_POST["i"]))
		$item_id = $_POST["i"];
	else if (isset ($_GET["i"]))
		$item_id = $_GET["i"];
	else
		header ("location: view_list.php?l=" . $list_id);

	if ($page_action == "insertComment") {
		$sql = "INSERT INTO ListItemComments (ListItemID, CommentDate, CommentAuthor, CommentText) " .
			"VALUES (" . $_POST["i"] . ",'" . date ("Y-m-d H:i:s") . "','" . $_SESSION["UserID"] .
			"','" . $_POST["comment_text"] . "')";
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		$sql = "UPDATE ListItems SET LastModified='" . date ("Y-m-d H:i:s") . "' WHERE ListItemID=" . $_POST["i"];
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		$sql = "UPDATE Lists SET LastModified='" . date ("Y-m-d H:i:s") . "' WHERE ListID=" . $_POST["l"];
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
                // insert a record into the history table
                $sql = "INSERT INTO NerderyEvents (EventTitle, EventDescription, UserID, EventTypeID, EventURL) VALUES (CONCAT('New comment in ', (SELECT ListTitle FROM Lists WHERE ListID=" . $_POST["l"] . ")), CONCAT('" . $_SESSION["user"]->displayName . "',' commented on the list ', (SELECT ListTitle FROM Lists WHERE ListID=" . $_POST["l"] . ")), '" . $_SESSION["UserID"] . "', (SELECT EventTypeID FROM NerderyEventType WHERE EventTypeName='ListItemComment'), CONCAT('/view_list_item.php?l=', " . $_POST["l"] . ", '&i=', " . $_POST["i"] . "))";
                mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
	}

	WriteHeader (2, "The Nerdery::View List Item");
	writeCP ();

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
			<span class="sectionHeader">
				<a href="lists.php" class="boldWhiteLink">All lists</a>
				&nbsp;&gt;&gt;&nbsp;
				<a href="view_list.php?l=<?php echo $list_id; ?>" class="boldWhiteLink">
					<?php echo substr ($curr_list->listTitle, 0, 30); ?>
				</a>
				&nbsp;&gt;&gt;&nbsp;List Item
			</span>
		</td>
		<td width="25"><img border="0" height="32" width="25" src="images/section.header.e.gif"></td>
	</tr>
	<tr>
		<td></td>
		<td align="center">
			<?php
				WriteListItem ($item_id, getListItemsDir(), $application);
			?>
		</td>
		<td></td>
	</tr>
	<tr><td colspan="3"><br></td></tr>


	<tr>
		<td width="25" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td>
						<span class="sectionHeader">
							<a href="lists.php" class="boldWhiteLink">All lists</a>
							&nbsp;&gt;&gt;&nbsp;
							<a href="view_list.php?l=<?php echo $list_id; ?>" class="boldWhiteLink"><?php echo substr ($curr_list->listTitle, 0, 30); ?></a>
							&nbsp;&gt;&gt;&nbsp;Comments
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
			writeListItemComments ($item_id, $application);
		?>
		</td>
		<td></td>
	</tr>

	<!-- form for adding a new comment -->
	<tr>
		<td width="25" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<span class="sectionHeader">
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
					array ("l", "", "hidden", $list_id, false),
					array ("i", "", "hidden", $item_id, false),
					array ("comment_text", "Comments", "textarea", "", true),
				);
				createForm ($fstruct, "field", "addComment", "view_list_item.php", true, "Add my $0.02", "");
			?>
		</td>
	</tr>

</table>


<?php
	WriteFooter ();

	$application->save();
?>