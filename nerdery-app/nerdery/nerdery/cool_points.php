<?php
	ob_start ();

	require ('includes/framework_functions.php');
	require ('includes/global_functions.php');

	session_set_save_handler ("open_session", "close_session", "read_session", "write_session",
		"destroy_session", "session_gc");
	session_start ();

	if ($_SESSION["ValidLogin"] != 1)
		header ("location: login.php?r=" . $_SERVER["PHP_SELF"]);
?>

<?php
	WriteHeader (1, "Nerdery Home", "User Cool Points<sup>TM</sup>");

	$target_user = $_SESSION["UserID"];
	if (isset ($_POST["userID"]))
		$target_user = $_POST["UserID"];
	else if (isset ($_GET["userID"]))
		$target_user = $_GET["userID"];

	if ($page_action == "assignPoints") {
		if ($_POST["plus_minus"] == "add")
			$add_in = 1;
		else
			$add_in = 0;
		$sql = "INSERT INTO UserCoolPoints (UserID, CoolPoints, AddPoints, Reason, ActionDate, GivenBy) " .
			"VALUES ('" . $_POST["userID"] . "'," . $_POST["number_points"] . "," . $add_in . 
			",'" . $_POST["reasonFor"] . "','" . date ("Y-m-d H:i:s") . "','" . $_SESSION["UserID"] . "')";
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
	}
?>
<table border="0" width="750" cellpadding="0" cellspacing="0">
	<tr>
		<td colspan="3" align="center">
			<table border="0" width="700" cellpadding="0" cellspacing="0">
<?php
	$sql = "SELECT P.*, F.DisplayName AS FromDisplayName FROM UserCoolPoints AS P, Users AS F " .
		"WHERE P.UserID=F.UserID AND P.UserID='" . $target_user . "' ORDER BY ActionDate DESC";
	$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
	$class = "evenCell";
	while ($row = mysql_fetch_array ($rs)) {
		$class = (($class == "oddCell") ? "evenCell" : "oddCell");
		echo "<tr>" .
						"<td class=\"$class\" align=\"center\">" .
							"<img height=\"50\" width=\"50\" src=\"/images/signatures/" .
								$row["GivenBy"] . ".sig\"><br><span class=\"smallBlueText\">" .
							date ("m-d-Y h:m", strtotime ($row["ActionDate"])) .
						"</span></td>" .
						"<td class=\"$class\">";
		if ($row["AddPoints"] == 0)
			echo "<span class=\"redText\">";
		else
			echo "<span class=\"blueText\">";
		echo 			$row["CoolPoints"] . "</span><br>" .
							$row["Reason"] .
						"</td>" .
				 "</tr>";
	}
?>
			</table>
		</td>
	</tr>
</table>

<?php
	WriteFooter ();
?>