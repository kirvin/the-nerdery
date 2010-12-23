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

	// set up vars for current list view
	$filter_date = 1;
	if (isset($_POST["filterDate"]))
		$filter_date = $_POST["filterDate"];
	$page_action = "";
	if (isset($_POST["paction"]))
		$page_action = $_POST["paction"];

	$errMsg = "";

	$ltitle_in = "";
	$ldesc_in = "";
	$ltype_in = 1;
	
	//---------------------------------------------------------------
	// Insert list if requested in form data 
	//---------------------------------------------------------------
	if ($page_action == "insertList") {
		$sql = "SELECT * FROM Lists WHERE ListOwner='" . $_SESSION["UserID"] . "' AND ListTitle='" . $_POST["list_title"] . "'";
		$rs = mysql_query ($sql);
		if (mysql_num_rows ($rs) > 0) {
			$errMsg = "You have already created a list with that title.  Please change the title of your new list.";
		}
		else {
			$now = getdate();
			$sql = "INSERT INTO Lists (ListTitle, ListDescription, ListOwner, CreatedDate, LastModified, IsPublic) VALUES ('" .
					$_POST["list_title"] . "','" . $_POST["list_description"] .
					//"','" . $_SESSION["UserID"] . "','" . date("Y-m-d H:m:s") . "'," . $_POST["list_type"] . ")";
					"','" . $_SESSION["UserID"] . "','" . date("Y-m-d H:m:s") . "','" . date("Y-m-d H:m:s") . "',1)";
			mysql_query ($sql) or die ("[Insert List] Error updating database: " . mysql_error());
		}
		if (strlen($errMsg) > 0) {
			$ltitle_in = $_POST["list_title"];
			$ltype_in = $_POST["list_type"];
			$ldesc_in = $_POST["list_description"];
		}
	}

	WriteHeader (2, "Nerdery Lists"); 
?>

<script language="javascript" type="text/javascript">
	function viewList (lid) {
		document.redirectForm.list_id.value = lid;
		document.redirectForm.submit();
	}
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
				<form name="filterForm" action="lists.php" method="post">
				<tr>
					<td>
						<span class="boldWhiteMediumText">
							Current Lists
						</span>
					</td>
					<td align="right">
						<span class="boldWhiteMediumText">View:&nbsp;</span>
						<select name="filterDate" onChange="document.filterForm.submit();">
						<?php
							if ($filter_date == 0) {
								echo "<option value=\"0\" selected>All Lists";
								echo "<option value=\"1\">Active Lists";
							}
							else {
								echo "<option value=\"0\">All Lists";
								echo "<option value=\"1\" selected>Active Lists";
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
			writeLists ($filter_date);
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
				Create a list
			</span>
		</td>
		<td width="25" align="right"><img border="0" height="32" width="25" src="images/section.header.e.gif"></td>
	</tr>
	<tr>
		<td colspan="3" align="center">
			<table border="0" cellpadding="0" cellspacing="0" width="700">
				<tr>
					<td>
						Fill out the form below and click on the "Create" button to add a new list for discussion and voting.  
						All new lists will appear in the section above.  Once you have created the list, you will be able to 
						add or modify the items	on the list.
					</td>
				</tr>
				<tr>
					<td>
						<br>
						&nbsp;&nbsp;<span class="boldGreenText">Public Lists</span> are open to all users, and anyone can add new candidates to the list.
						<br>
						&nbsp;&nbsp;<span class="boldGreenText">Private Lists</span> can only be modified by the users who created them.
						<br><br>
					</td>
				</tr>
			</table>
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
								array ("paction", "", "hidden", "insertList", false),
								array ("filterDate", "", "hidden", $filter_date, false),
								array ("list_type", "List Type", "radio", array (array(0,"Private List", false), array (1, "Public List", true)), true), 
								array ("list_title", "List Title", "text", $ltitle_in, true),
								array ("list_description", "List Description", "textarea", $ldesc_in, true)
							);
							createForm ($fstruct, "field", "addList", "lists.php", true, "Create List", "");
						?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>


<?php
	WriteFooter ();
?>
