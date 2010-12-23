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
		header ("location: ../drteeth/login.php?r=" . $_SERVER["PHP_SELF"]);

	WriteHeader (5, "The Nerdery::News"); 

?>

<table border="0" width="750" cellpadding="0" cellspacing="0">
	<tr>
		<td width="25" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td>
						<span class="boldWhiteMediumText">
							<a href="news.php" class="boldWhiteLink">All News Columns</a>
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
			writeAllNews ();
		?>
		</td>
		<td></td>
	</tr>
	<tr><td colspan="3"><br></td></tr>
	<tr>
		<td width="25" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<span class="boldWhiteMediumText">
				Create A News Column
			</span>
		</td>
		<td width="25"><img border="0" height="32" width="25" src="images/section.header.e.gif"></td>
	</tr>
	<tr>
		<td align="center" colspan="3">
			<?php
				$fstruct = array (
					array ("paction", "", "hidden", "insertNewsColumn", false),
					array ("column_title", "Title", "text", "", true),
					array ("column_description", "Description", "textarea", "", true),
				);
				createForm ($fstruct, "field", "addList", "view_news.php", true, "Create Column", "");
			?>
		</td>
	</tr>
</table>


<?php
	WriteFooter ();
?>
