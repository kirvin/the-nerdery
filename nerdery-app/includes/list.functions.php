<?php
/**
 *	
 *
 *
 */
class VotingList {
	var $listID;
	var $listTitle;
	var $listDescription;
	var $listOwner;
	var $createdDate;
	var $listItems;
	var $itemCount;
	var $isPublic;
	var $lastModified;

	/**
	 * 
	 *
	 * @param unknown_type $list_id
	 * @return VotingList
	 */
	function VotingList ($list_id) {
		$exists = false;
		$sql = "SELECT * FROM Lists WHERE ListID=" . $list_id;
		$rs = mysql_query ($sql);
		if (mysql_num_rows ($rs) > 0) {
			$row = mysql_fetch_array ($rs);
			$this->listID = $row["ListID"];
			$this->listTitle = $row["ListTitle"];
			$this->listDescription = $row["ListDescription"];
			$this->listOwner = $row["ListOwner"];
			$this->listCreationDate = $row["CreatedDate"];
			$this->isPublic = $rs["IsPublic"];
			$this->lastModified = $rs["LastModified"];
			$this->listItems = getListItems ($this->listID, $this->isPublic);
			$this->itemCount = count($this->listItems);
			$exists = true;
		}
		return $exists;
	}
}

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
 * 
 * @author drteeth
 *
 */
class ListItem {
	var $ListItemID;
	var $ListItemText;
	var $ListItemURL;
	var $ListItemOwner;
	var $ListItemDate;
	var $OwnerName;
	var $ListItemFile;
	
	function ListItem ($liid, $lit, $liu, $liow, $lid, $ow, $lif, $lm) {
		$this->ListItemID = $liid;
		$this->ListItemText = $lit;
		$this->ListItemURL = $liu;
		$this->ListItemOwner = $liow;
		$this->ListItemDate = $lid;
		$this->OwnerName = $ow;
		$this->ListItemFile = $lif;
		$this->LastModified = $lm;
	}
}

/**
 * 
 *
 */
function writeLists ($f_date) {
	if ($f_date == 1) {
		$date = mktime (0,0,0,date("m"),date("d")-14,date("Y"));
		$sql = "SELECT L.*, U.DisplayName FROM Lists AS L INNER JOIN Users AS U ON L.ListOwner=U.UserID WHERE LastModified > '" . (date("Y-m-d h:i:s", $date)) . "' ORDER BY LastModified DESC, ListTitle ASC";
	}
	else
		$sql = "SELECT L.*, U.DisplayName FROM Lists AS L INNER JOIN Users AS U ON L.ListOwner=U.UserID ORDER BY LastModified DESC, ListTitle ASC";
	$rs = mysql_query ($sql) or die ("[writeLists]  error getting result set");
	print "<table width=\"95%\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\">";
	if (mysql_num_rows ($rs) > 0) {
		$curr_class = "evenCell";
		while ($row = mysql_fetch_array ($rs)) {
			$sql = "SELECT COUNT(ListItemID) AS NewItems FROM ListItems WHERE ListID=" . $row["ListID"] .
				" AND LastModified > '" . $_SESSION["PrevVisit"] . "'";
			$cnt_rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
			$cnt_row = mysql_fetch_array ($cnt_rs);
			$aclass = (($row["LastModified"] > $_SESSION["PrevVisit"]) ? "boldGreenLink" : "boldBlueLink");

			$curr_class = (($curr_class == "oddCell") ? "evenCell" : "oddCell");
			echo "<tr><td width=\"70%\" colspan=\"2\" valign=\"top\" align=\"left\" class=\"" . $curr_class . 
				"\"><a class=\"$aclass\" href=\"view_list.php?l=" . $row["ListID"] . "\">" .
				$row["ListTitle"] . "</a>&nbsp;<span class=\"blueText\">(" . $cnt_row["NewItems"] . " new)</span></td><td align=\"right\" " .
				"width=\"30%\" class=\"" . $curr_class . "\"><span class=\"boldBlueText\">" . date("m/d/Y", strtotime ($row["CreatedDate"])) . 
				"</span></td></tr><tr><td width=\"5%\" class=\"" . $curr_class . "\"><td colspan=\"2\" class=\"" . $curr_class . "\">" . 
				$row["ListDescription"] . " (" . $row["DisplayName"] . ")<br><br></td></tr>";
		}
	}
	else {
		echo "<tr><td align=\"center\">There are not currently any lists open.<br><br></td></tr>";
	}
	print "</table>";
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
