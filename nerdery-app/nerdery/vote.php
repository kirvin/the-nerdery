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
	WriteHeader (1, "Nerdery Voting");
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
		<td colspan="3">
			<br><br><br><br>
			This page is probably going away.  Nothing here right now.
			<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
		</td>
	</tr>
</table>

<?php
	WriteFooter ();
?>