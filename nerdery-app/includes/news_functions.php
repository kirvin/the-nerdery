<?php

//------------------------------------------------------------------------
// Writes out all news columns
//------------------------------------------------------------------------
function writeAllNews () {
	$sql = "SELECT * FROM Journals ORDER BY JournalTitle";
	$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
	
	echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\">";
	
	$class = "evenCell";
	while ($row = mysql_fetch_array ($rs)) {
		$class = (($class == "oddCell") ? "evenCell" : "oddCell");
		if ($row["LastModified"] > $_SESSION["PrevVisit"])
			$aclass = "boldGreenLink";
		else
			$aclass = "boldBlueLink";
		echo "<tr>" .
				"<td colspan=\"2\" class=\"$class\">" .
					"<a class=\"$aclass\" href=\"view_news.php?j=" . $row["JournalID"] . "\">" . $row["JournalTitle"] . "</a>" .
				"</td>" .
			 "</tr>" .
			 "<tr>" .
			 	"<td width=\"15\" class=\"$class\"></td><td class=\"$class\">" .
					"<span class=\"smallBlackText\">" . $row["JournalDescription"] . "</span>" .
				"</td>" .
			 "</tr>";
	}

	echo "</table>";
}

//------------------------------------------------------------------------
// Writes out all items for a specific news column
//------------------------------------------------------------------------
function writeNewsColumn ($jid) {
	$sql = "SELECT E.*, U.* FROM JournalEntries AS E, Users AS U WHERE U.UserID=E.JournalEntryAuthor AND JournalID=$jid ORDER BY JournalEntryDate DESC";
	$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
	
	echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" width=\"100%\">";
	$class = "evenCell";
	while ($row = mysql_fetch_array ($rs)) {
		$class = (($class == "oddCell") ? "evenCell" : "oddCell");
		if ($row["LastModified"] > $_SESSION["PrevVisit"])
			$aclass = "boldGreenLink";
		else
			$aclass = "boldBlueLink";
		echo "<tr>" .
				"<td rowspan=\"2\" class=\"$class\" align=\"center\" valign=\"top\">" .
					"<img width=\"50\" src=\"/images/signatures/" . $row["JournalEntryAuthor"] . ".sig\">" .
					"<br><span class=\"smallBlueText\">" .
					date ("m/d/Y h:i a", strtotime ($row["JournalEntryDate"])) .
					"</span>" .
				"</td>" .
				"<td class=\"$class\">" .
					"<a class=\"$aclass\" href=\"view_news_item.php?j=" . $row["JournalID"] . "&e=" . $row["JournalEntryID"] .
						"\">" . $row["JournalEntrySubject"] . "</a>" .
				"</td>" .
			 "</tr>" .
			 "<tr>" .
			 	"<td class=\"$class\">" .
					"<span class=\"smallBlackText\">" . str_replace ("\n\r", "<p>", substr (strip_tags ($row["JournalEntryText"]), 0, 250)) . "...</span>" .
				"</td>" .
			 "</tr>";
	}
	echo "</table>";
}

//------------------------------------------------------------------------
// Writes out a single news item
//------------------------------------------------------------------------
function writeNewsItem ($jid, $jed) {
	$sql = "SELECT E.*, U.* FROM JournalEntries AS E, Users AS U WHERE U.UserID=E.JournalEntryAuthor AND " .
		"JournalEntryID=$jid";
	$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
	
	echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" width=\"100%\">";
	$class = "evenCell";
	while ($row = mysql_fetch_array ($rs)) {
		$class = (($class == "oddCell") ? "evenCell" : "oddCell");
		if ($row["LastModified"] > $_SESSION["PrevVisit"])
			$aclass = "boldGreenLink";
		else
			$aclass = "boldBlueLink";
		echo "<tr>" .
				"<td width=\"90\" rowspan=\"2\" class=\"$class\" align=\"center\" valign=\"top\">" .
					"<img width=\"50\" src=\"/images/signatures/" . $row["JournalEntryAuthor"] . ".sig\">" .
					"<br><span class=\"smallBlueText\">" .
					date ("m/d/Y h:i a", strtotime ($row["JournalEntryDate"])) .
					"</span>" .
				"</td>" .
				"<td class=\"$class\">" .
					"<a class=\"$aclass\" href=\"view_news_item.php?j=" . $row["JournalID"] . "&e=" . $row["JournalEntryID"] .
						"\">" . $row["JournalEntrySubject"] . "</a>" .
				"</td>" .
			 "</tr>" .
			 "<tr>" .
			 	"<td class=\"$class\">";
		if ($row["FileExtension"] !== "") {
			echo "<img align=\"right\" src=\"" . $jed . "/" . $row["JournalID"] . "/" . $row["JournalEntryID"] . "." . 
				$row["FileExtension"] . "\">";
		}
		echo	"<span class=\"smallBlackText\">" . str_replace ("\n\r", "<p>", $row["JournalEntryText"]) . "...</span>" .
				"</td>" .
			 "</tr>";
	}
	echo "</table>";
}

//------------------------------------------------------------------------
// Writes out comments for a journal entry (news item)
//------------------------------------------------------------------------
function writeJournalEntryComments ($eid) {
	$sql = "SELECT E.*, U.DisplayName FROM JournalEntryComments AS E, Users AS U WHERE E.CommentAuthor=U.UserID AND JournalEntryID=" . $eid;
	$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
	
	echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" width=\"100%\">";
	if (mysql_affected_rows() == 0) {
		echo "<tr><td align=\"center\">No comments have been posted for this entry.<br><br></td></tr>";
	}
	else {
		$class = "evenCell";
		while ($row = mysql_fetch_array ($rs)) {
			$class = (($class == "oddCell") ? "evenCell" : "oddCell");
			if ($row["JournalEntryDate"] > $_SESSION["PrevVisit"])
				$tclass = "boldGreenText";
			else
				$tclass = "boldBlueText";
			echo "<tr>" .
					"<td width=\"90\" class=\"$class\" align=\"center\" valign=\"top\">" .
						"<img width=\"50\" src=\"/images/signatures/" . $row["CommentAuthor"] . ".sig\">" .
						"<br><span class=\"smallBlackText\">" . $row["DisplayName"] . "</span>" .
						"<br><span class=\"smallBlueText\">" .
						date ("m/d/Y h:i a", strtotime ($row["CreatedDate"])) .
						"</span>" .
					"</td>" .
				 	"<td class=\"$class\" valign=\"top\">" .
						"<span class=\"smallBlackText\">" . str_replace ("\n\r", "<p>", $row["CommentText"]) . "</span>" .
					"</td>" .
				 "</tr>";
		}
	}
	echo "</table>";
}

?>