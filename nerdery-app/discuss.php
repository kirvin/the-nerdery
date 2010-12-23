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

	// set up vars for current list view
	$filter_date = 1;
	if (isset($_POST["filterDate"]))
		$filter_date = $_POST["filterDate"];
	$page_action = "";
	if (isset($_POST["paction"]))
		$page_action = $_POST["paction"];

	$errMsg = "";

	WriteHeader (4, "The Nerdery::Discussions"); 
	writeCP ();
?>

<script language="javascript" type="text/javascript">
	function viewDiscussion (did) {
		document.redirectForm.discussion_id.value = did;
		document.redirectForm.submit ();
	}
</script>

<form name="redirectForm" action="view_discussion.php" method="post">
	<input type="hidden" name="paction" value="">
	<input type="hidden" name="discussion_id" value="">
</form>

<table border="0" width="750" cellpadding="0" cellspacing="0">
	<tr>
		<td width="25" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<form name="filterForm" action="discuss.php" method="post">
				<tr>
					<td>
						<span class="boldWhiteMediumText">
						<?php
							if ($filter_date == 0)
								$sql = "SELECT COUNT(*) AS DiscussionCount FROM Discussions";
							else {
								$date = mktime (0,0,0,date("m"),date("d")-14,date("Y"));
								$sql = "SELECT COUNT(*) AS DiscussionCount FROM Discussions WHERE LastModified > '" . date("Y-m-d", $date) . "'";
							}
							$rs = mysql_query ($sql);
							$row = mysql_fetch_array ($rs);
							$discussion_count = $row["DiscussionCount"];
						?>
							Current Discussions (<?php echo $discussion_count; ?> found)
						</span>
					</td>
					<td align="right">
						<span class="boldWhiteMediumText">View:&nbsp;</span>
						<select name="filterDate" onChange="document.filterForm.submit();">
						<?php
							if ($filter_date == 0) {
								echo "<option value=\"0\" selected>All Discussions";
								echo "<option value=\"1\">Active Discussions";
							}
							else {
								echo "<option value=\"0\">All Discussions";
								echo "<option value=\"1\" selected>Active Discussions";
							}
						?>
						</select>
					</td>
				</tr>
				</form>
			</table>
		</td>
		<td width="25"><img border="0" height="32" width="25" src="images/section.header.e.gif"></td>
	</tr>
	<tr>
		<td colspan="3" align="center">
		<?php
			writeDiscussions ($filter_date);
		?>
		<br>
		</td>
	</tr>
</table>


<table border="0" width="750" cellpadding="0" cellspacing="0">
	<tr>
		<td width="25"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<span class="boldWhiteMediumText">
				Create a discussion
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
								array ("paction", "", "hidden", "insertDiscussion", false),
								array ("filterDate", "", "hidden", $filter_date, false),
								array ("discussion_title", "Title", "text", $ltitle_in, true),
								array ("discussion_description", "Description", "textarea", $ldesc_in, true)
							);
							createForm ($fstruct, "field", "addDiscussion", "view_discussion.php", true, "Create Discussion", "");
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
