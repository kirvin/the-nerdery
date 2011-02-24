<?php
/**
 * Retrieves the items in a Nerdery List
 *
 * @param unknown_type $lid
 * @param unknown_type $ip
 * @return unknown
 */
function getListItems ($lid, $ip) {
	$answer = array ();
	$count = 0;
	$sql = "SELECT ListItemID, ListItemText, ListItemURL, ListItemFile, ListItemOrder, ListItemOwner, LastModified, " .
		"U.*, CreatedDate FROM ListItems AS L JOIN Users AS U " .
		"ON L.ListItemOwner=U.UserID WHERE ListID=" . $lid . " ORDER BY CreatedDate DESC, " .
		"ListItemText ASC";
	$rs = mysql_query ($sql) or die ("SQL ERROR: " . mysql_error());
	if (mysql_num_rows ($rs) > 0) {
		$row = mysql_fetch_array ($rs);
		while ($row) {
			$answer[$count] = new ListItem ($row["ListItemID"], $row["ListItemText"], $row["ListItemURL"],
				$row["ListItemOwner"], $row["CreatedDate"], $row["DisplayName"],
				$row["ListItemFile"], $row["LastModified"]);
			$row = mysql_fetch_array ($rs);
			$count++;
		}
	}
	return $answer;
}

/**
 * Writes out the html for items in a list.
 *
 * @param unknown_type $target_list
 */
function writeListItems ($target_list) {
	echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" width=\"100%\">";
	$curr_class = "evenCell";
	for ($i=0; $i<count($target_list->listItems); $i++) {		$curr_class = (($curr_class == "oddCell") ? "evenCell" : "oddCell");		echo "<tr><td valign=\"top\" width=\"25%\" class=\"" . $curr_class . "\" align=\"right\">";			if ($target_list->listItems[$i]->LastModified > $_SESSION["PrevVisit"]) {				$aclass = "boldGreenLink";				echo "<img src=\"images/yellow_bullet.gif\">";			}			else {				$aclass = "boldBlueLink";				echo "<img src=\"images/green_bullet.gif\">";			}			echo "</td>" .					 "<td class=\"" . $curr_class . "\">" .						"<a class=\"$aclass\" href=\"view_list_item.php?l=" . $target_list->listID . "&i=" . 							$target_list->listItems[$i]->ListItemID . "\">" . 							$target_list->listItems[$i]->ListItemText . "</a>" .			"</td>" .			"<td class=\"$curr_class\" align=\"right\" valign=\"top\">" .				$target_list->listItems[$i]->OwnerName .			"</td>" .		 "</tr>";	}
	echo "</table>";
}


/**
 * Writes out the details and comments of a list item
 *
 * @param unknown_type $item_id
 * @param unknown_type $lid
 */
function writeListItem ($item_id, $lid, &$application) {
	$sql = "SELECT I.*, U.* FROM ListItems AS I, Users AS U WHERE U.UserID=I.ListItemOwner AND " .
			"ListItemID=$item_id";
	$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
	$row = mysql_fetch_array ($rs);
	echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" width=\"100%\">" .
					"<tr>" .
						"<td width=\"90\" rowspan=\"2\" class=\"oddCell\" align=\"center\" valign=\"top\">" .
							"<img src=\"/images/signatures/" . $row["ListItemOwner"] . ".sig\">" .
							"<br><span class=\"smallBlueText\">" .
								date ("m/d/Y h:i a", strtotime ($row["CreatedDate"])) .
							"</span>";
	if ($application->getTopTimeWaster() == $row["ListItemOwner"]) {
		echo			"<div align=\"center\"><img src=\"images/top_waster.gif\"></div>	";
	}
	echo
						"</td>" .
						"<td class=\"oddCell\">" .
							$row["ListItemText"] .
						"</td>" .
					 "</tr>";
	//echo "<tr><td>";	//print_r ($row);	//echo "</td></tr>";
	if ($row["ListItemFile"] !== "") {
		echo "<tr>" .
				 	"<td class=\"oddCell\">";
		$ftype = $row["ListItemFile"];
		$ftype = strtoupper ($ftype);
		if ($ftype == "JPG" || $ftype == "JPEG" || $ftype == "GIF" || $ftype == "BMP") {
			echo	"<img width=\"500\" src=\"" . getListItemsDir() . "/" . $row["ListID"] . "/" . $row["ListItemID"] . "." .
 						$row["ListItemFile"] . "\">";
		}
		else {
			echo 	"<a class=\"blueLink\" href=\"/misc/listitems/" . $row["ListID"] . "/" . $row["ListItemID"] . "." .
						$row["ListItemFile"] . "\">" . $row["ListItemText"] . "</a>";
		}
		"</td>" .
			 "</tr>";
	}
	else if ($row["ListItemURL"] !== "") {
		echo 	"<tr>" .
					"<td class=\"oddCell\">" .
						"<a target=\"_blank\" class=\"blueLink\" href=\"" . $row["ListItemURL"] . "\">" .
 							$row["ListItemText"] .
 						"</a>" .
					"</td>" .
				"</tr>";
	}
	echo "</table>";
}


//------------------------------------------------------------------------
// Writes out comments for a list item
//------------------------------------------------------------------------
/**
 *
 *
 * @param unknown_type $eid
 * @param unknown_type $application
 */
function writeListItemComments ($eid, $application) {
	$sql = "SELECT * FROM ListItemComments WHERE ListItemID=" . $eid . " ORDER BY CommentDate";	$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
	echo "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\" width=\"100%\">";	if (mysql_affected_rows() == 0) {		echo "<tr><td align=\"center\">No comments have been posted for this entry.<br><br></td></tr>";	}	else {		$class = "evenCell";		while ($row = mysql_fetch_array ($rs)) {			$class = (($class == "oddCell") ? "evenCell" : "oddCell");			if ($row["CommentDate"] > $_SESSION["PrevVisit"])				$tclass = "boldGreenText";			else				$tclass = "boldBlueText";			echo "<tr>" .					"<td width=\"90\" class=\"$class\" align=\"center\" valign=\"top\">" .						"<img src=\"/images/signatures/" . $row["CommentAuthor"] . ".sig\">" .						"<br><span class=\"smallBlueText\">" .						date ("m/d/Y h:i a", strtotime ($row["CommentDate"])) .						"</span>";	if ($application->getTopTimeWaster() == $row["CommentAuthor"]) {
		echo			"<div align=\"center\"><img alt=\"Top Time Waster\" src=\"images/top_waster.gif\"></div>";
	}
	echo
					"</td>" .				 	"<td class=\"$class\" valign=\"top\">" .						"<span class=\"smallBlackText\">" . str_replace ("\n\r", "<p>", $row["CommentText"]) . "</span>" .					"</td>" .				 "</tr>";		}	}	echo "</table>";}

?>
