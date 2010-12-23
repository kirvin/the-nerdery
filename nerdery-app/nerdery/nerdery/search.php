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
	WriteHeader (3, "Nerdery Home", "Search the Nerdery");


	$frm = new Form ("field", "searchForm", "search.php", "runSearch");
	$frm->addFormElement ("search_term", "text", "Search For", "", true);
	$frm->method = "get";
	$frm->draw();

	echo "<br><div align=\"center\">";
	if ($page_action == "runSearch") {
		// search lists
		$sql = "SELECT L.*, I.* FROM Lists AS L, ListItems AS I WHERE ListItemText LIKE '%" . $_GET["search_term"] . 
			"%' AND L.ListID=I.ListID";
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);

		writeSectionHeader ("Lists and List Items");
		echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"2\" width=\"700\">";
		if (mysql_num_rows($rs) > 0) {
			$class = "evenCell";
			$count = 0;
			while ($row = mysql_fetch_array ($rs)) {
				$class = (($class == "evenCell") ? "oddCell" : "evenCell");
				//http://nerderydev.theirvins.net/view_list_item.php?l=95&i=1493
				echo 	"<tr>" .
								"<td class=\"$class\"><b>" .
									++$count . ".</b>&nbsp;" .
									"<a href=\"view_list_item.php?l=" . $row["ListID"] . "&i=" . $row["ListItemID"] .
										"\">" . highlightText (strip_tags ($row["ListItemText"]), $_GET["search_term"], true) . "</a>" .
								"</td>" .
							"</tr>";
			}
		}
		else {
			echo 	"<tr>" .
							"<td align=\"center\">" .
								"No list items matched your search.<br><br>" .
							"</td>" .
						"</tr>";
		}
		// new query for list item comments, displayed under the same header
		$sql = "SELECT L.*, C.*, I.* FROM Lists AS L, ListItemComments AS C, ListItems AS I WHERE CommentText LIKE '%" . $_GET["search_term"] . 
			"%' AND C.ListItemID=I.ListItemID AND L.ListID=I.ListID";
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		if (mysql_num_rows($rs) > 0) {
			while ($row = mysql_fetch_array ($rs)) {
				$class = (($class == "evenCell") ? "oddCell" : "evenCell");
				//http://nerderydev.theirvins.net/view_list_item.php?l=95&i=1493
				echo 	"<tr>" .
								"<td class=\"$class\"><b>" .
									++$count . ".</b>&nbsp;" .
									"<a href=\"view_list_item.php?l=" . $row["ListID"] . "&i=" . $row["ListItemID"] .
										"\">" . highlightText (strip_tags ($row["CommentText"]), $_GET["search_term"], true) . "</a>" .
								"</td>" .
							"</tr>";
			}
		}
		echo "</table>";


		//search discussion comments
		$sql = "SELECT * FROM DiscussionComments WHERE CommentSubject LIKE '%" . $_GET["search_term"] . 
			"%' OR CommentText LIKE '%" . $_GET["search_term"] . "%'";
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		writeSectionHeader ("Discussions");
		echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"2\" width=\"700\">";
		if (mysql_num_rows($rs) > 0) {
			$class = "evenCell";
			$count = 0;
			while ($row = mysql_fetch_array ($rs)) {
				$class = (($class == "evenCell") ? "oddCell" : "evenCell");
				echo 	"<tr>" .
								"<td class=\"$class\"><b>" .
									++$count . ".</b>&nbsp;" .
									"<a href=\"view_thread.php?d=" . $row["DiscussionID"] . "&t=" . floor($row["CommentID"]) .
										"&p=1\">" . highlightText (strip_tags ($row["CommentSubject"]), $_GET["search_term"], true) . "</a>" .
								"</td>" .
							"</tr>";
			}
		}
		else {
			echo 	"<tr>" .
							"<td align=\"center\">" .
								"No discussion comments matched your search." .
							"</td>" .
						"</tr>";
		}
		echo "</table>";


		//search news items
		$sql = "SELECT * FROM JournalEntries WHERE JournalEntryText LIKE '%" . $_GET["search_term"] . 
			"%' OR JournalEntrySubject LIKE '%" . $_GET["search_term"] . "%'";
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		writeSectionHeader ("News and News Discussions");
		echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"2\" width=\"700\">";
		if (mysql_num_rows($rs) > 0) {
			$class = "evenCell";
			$count = 0;
			while ($row = mysql_fetch_array ($rs)) {
				$class = (($class == "evenCell") ? "oddCell" : "evenCell");
				//http://nerderydev.theirvins.net/view_news_item.php?j=8&e=211
				echo 	"<tr>" .
								"<td class=\"$class\"><b>" .
									++$count . ".</b>&nbsp;" .
									"<a href=\"view_news_item.php?j=" . $row["JournalID"] . "&e=" . $row["JournalEntryID"] .
										"\">" . highlightText (strip_tags ($row["JournalEntryText"]), $_GET["search_term"], true) . "</a>" .
								"</td>" .
							"</tr>";
			}
		}
		else {
			echo 	"<tr>" .
							"<td align=\"center\">" .
								"No news items matched your search." .
							"</td>" .
						"</tr>";
		}
		$sql = "SELECT C.*, E.*, J.* FROM Journals AS J, JournalEntries AS E, JournalEntryComments AS C WHERE CommentText LIKE '%" . $_GET["search_term"] . 
			"%' AND J.JournalID=E.JournalID AND E.JournalEntryID=C.JournalEntryID";
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
		if (mysql_num_rows($rs) > 0) {
			$class = "evenCell";
			while ($row = mysql_fetch_array ($rs)) {
				$class = (($class == "evenCell") ? "oddCell" : "evenCell");
				//http://nerderydev.theirvins.net/view_news_item.php?j=8&e=211
				echo 	"<tr>" .
								"<td class=\"$class\"><b>" .
									++$count . ".</b>&nbsp;" .
									"<a href=\"view_news_item.php?j=" . $row["JournalID"] . "&e=" . $row["JournalEntryID"] .
										"\">" . highlightText (strip_tags ($row["CommentText"]), $_GET["search_term"], true) . "</a>" .
								"</td>" .
							"</tr>";
			}
		}


		echo "</table>";


	}
	echo "</div>";
	
	
	WriteFooter ();
?>