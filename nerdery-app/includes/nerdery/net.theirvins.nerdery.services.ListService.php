<?php
require_once ("net.theirvins.util.MySQLUtil.php");
require_once("net.theirvins.nerdery.domain.VotingList.php");
require_once("net.theirvins.nerdery.domain.ListItem.php");
require_once("net.theirvins.nerdery.services.BaseService.php");
require_once("net.theirvins.nerdery.domain.NerderyList.php");

/**
 * Provides access to Lists
 *
 * @author drteeth
 *
 */
class ListService extends BaseService {

	private static $instance;


	/**
	 * Gets a reference to the service singleton
	 */
	public static function getInstance () {
		if (is_null(self::$instance))
			self::$instance = new self();
		return self::$instance;
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
	 * Returns true if a list with this title has already been created by this user
	 *
	 * @param $userId
	 * @param $listTitle
	 */
	function listExists ($userId, $listTitle) {
		$sql = "SELECT * FROM Lists WHERE ListOwner='" . $userId . "' AND ListTitle='" . mysql_escape_string($listTitle) . "'";
		$rs = mysql_query ($sql);
		return (mysql_num_rows ($rs) > 0);
	}

	/**
	 * Creates a new list from the provided parameters.
	 *
	 * Returns the id of the newly created list if successful, null otherwise.
	 */
	function createList ($listTitle, $listDescription, User $user) {
		$now = getdate();
		$sql = "INSERT INTO Lists (ListTitle, ListDescription, ListOwner, CreatedDate, LastModified, IsPublic) VALUES ('" .
				mysql_escape_string($listTitle) . "','" . mysql_escape_string($listDescription) .
				"','" . $user->userID . "','" . date("Y-m-d H:m:s") . "','" . date("Y-m-d H:m:s") . "',1)";
		mysql_query ($sql) or die ("[Insert List] Error updating database: " . mysql_error());
		$newListId = mysql_insert_id();

		// insert a record into the history table
		$sql = "INSERT INTO NerderyEvents (EventTitle, EventDescription, UserID, EventTypeID, EventURL) " .
			"VALUES ('New List: \"" . mysql_escape_string(substr($listTitle, 0, 50)) . "\"', '" .
			$user->displayName . " created a new list.', '" . $user->userID . "', " .
			"(SELECT EventTypeID FROM NerderyEventType WHERE EventTypeName='List'), '/view_list.php?l=" .
			$newListId . "')";
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);

		if (mysql_error() !== "") {
			return null;
		}
		else {
			return $newListId;
		}
	}

}