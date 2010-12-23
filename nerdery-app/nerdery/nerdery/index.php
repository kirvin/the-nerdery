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
	WriteHeader (1, "Nerdery Home", "Since you were last here...");
?>
<table border="0" width="750" cellpadding="0" cellspacing="0">
	<tr>
		<td colspan="3">
		<?php
			echo "You were last here on " . date ("D, M d, Y  h:m:s", strtotime ($_SESSION["PrevVisit"]));
		?>
			<br><br><br><br>
			This page will be filled in soon...
			<br><br><br><br><br><br><br>

		</td>
	</tr>
</table>

<?php
	WriteFooter ();
?>