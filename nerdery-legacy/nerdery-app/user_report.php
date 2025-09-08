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

WriteHeader (1, "Nerdery Users");
writeCP ();

?>

<table border="0" width="750" cellpadding="0" cellspacing="0">
	<tr>
		<td width="25" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<span class="boldWhiteMediumText">
				Your fellow Nerderyers
			</span>
		</td>
		<td width="25"><img border="0" height="32" width="25" src="images/section.header.e.gif"></td>
	</tr>
	<tr>
		<td></td>
		<td>
<?php
	$sql = "SELECT U.*, MAX(C.ActionDate) AS LastCoolPoints, S.SessionTime FROM (Users AS U LEFT OUTER JOIN UserSessions AS S ON U.UserID=S.UserID) LEFT JOIN UserCoolPoints AS C ON C.UserID=U.UserID GROUP BY C.UserID, U.UserID ORDER BY PrevVisit DESC";
	$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);

	$class = "evenCell";
	echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" width=\"100%\">";
	while ($row = mysql_fetch_array ($rs)) {
		$class = (($class == "oddCell") ? "evenCell" : "oddCell");
		$sclass = (($row["SessionTime"] !== null) ? "boldBlueText" : "blueText");
		$img_name = (($row["UserSignature"] == 0) ? "no.sig" : $row["UserID"] . ".sig");
		echo "<tr>" .
					"<td width=\"50\" class=\"$class\">" .
						"<img width=\"50\" src=\"/images/signatures/$img_name\">" .
					"</td>" .
					"<td class=\"$class\">" .
						"<span class=\"$sclass\">" . $row["DisplayName"] . "</span>" .
					"</td>" .
					"<td class=\"$class\">" .
						"<span class=\"$sclass\">" . date ("m/d/Y h:i a", strtotime ($row["PrevVisit"])) . "</span>" .
					"</td>" .
					"<td class=\"$class\" align=\"center\">" .
						"<span class=\"$sclass\">" . $row["TotalTime"] . " minutes wasted</span>" .
					"</td>" .
					"<td class=\"$class\" align=\"right\">" .
						"<span class=\"$sclass\">";
						//print_r ($row);
		if ($row["LastCoolPoints"] > $_SESSION["PrevVisit"])
			echo "<a class=\"boldGreenLink\" href=\"cool_points.php?userID=" . $row["UserID"] . "\">" . $row["CoolPoints"] . "</a>";
		else
			echo "<a class=\"blueLink\" href=\"cool_points.php?userID=" . $row["UserID"] . "\">" . $row["CoolPoints"] . "</a>";
		echo          "</span>" .
					"</td>" .
				 "</tr>";
	}
	echo "</table>";
?>
		</td>
		<td></td>
	</tr>
</table>

<?php
	WriteFooter ();

	$application->save();
?>