<?php
require_once("nerdery/net.theirvins.util.Properties.php");
$props = Properties::getInstance();
$props->loadFromFile();

$debug = ($props->getProperty("debug") == "true");
if ($debug) {
	ini_set("error_reporting", E_ALL);
}

date_default_timezone_set('America/Los_Angeles');

require_once ("nerdery/net.theirvins.nerdery.Application.php");
require_once ("nerdery/net.theirvins.nerdery.domain.User.php");

	$COOKIE_LIFETIME = 1800;
	$IMAGES_DIR = "../images/gallery/";
	$SPOTLIGHT_DIR = "../images/spotlight/";

	$TEMP_DIR = "./misc/temp/";
	$UPLOADS_DIR = "/opt/www/uploads/nerdery";
	$JOURNALENTRIES_DIR = $UPLOADS_DIR . "/journalentries";
	$LIST_ITEMS_DIR = $UPLOADS_DIR . "/listitems/";

	$IMAGE_WIDTH = 550;
	$THUMB_WIDTH = 80;
	$SPOTLIGHT_WIDTH = 200;
	$SIGNATURE_WIDTH = 50;

	$MYSQL_DATE_FORMAT = "Y-m-d h:i:s";

	$db_server = $props->getProperty("db.server");
	$db_uid = $props->getProperty("db.username");
	$db_pwd = $props->getProperty("db.password");
	$db_name = $props->getProperty("db.name");


	/**
	 * Checks to make sure 1) the current page is not the login page or 2) the user
	 * is logged in.  If not, then the request is redirected to the login page.
	 */
	function checkSession () {
		if (strcmp($_SERVER["PHP_SELF"], '/login.php') != 0) {
			if ($_SESSION["ValidLogin"] != 1)
				header ("location: login.php?r=" . $_SERVER["PHP_SELF"]);
		}
	}


	//-------------------------------------------------------------------------
	// Functions for session handling
	//-------------------------------------------------------------------------
	function open_session ($sess_path, $session_name) {
		return (true);
	}
	//--------------------------------------------------------------------
	// Get session state for $_SESSION["UserID"] out of the db.  If the
	// session record is valid (i.e. time stamp is within valid session
	// lifetime range) then set session variables and $_SESSION["ValidLogin"]
	// to true.  Otherwise flag for invalid login state, and calling page
	// will redirect to login page.
	//--------------------------------------------------------------------
	function read_session ($key) {
		global $COOKIE_LIFETIME;
		$db = mysql_connect($GLOBALS["db_server"], $GLOBALS["db_uid"], $GLOBALS["db_pwd"]) or die("Could not connect : " . mysql_error());
		mysql_select_db($GLOBALS["db_name"]) or die("Could not select database");
		$query = "SELECT S.*, U.*, UT.UserTypeName FROM UserTypes AS UT, UserSessions AS S INNER JOIN Users AS U ON U.UserID=S.UserID WHERE S.SessionID='" . $key . "' AND UT.UserTypeID=U.UserTypeID";
		$result = mysql_query($query) or die("Query failed : " . mysql_error() . "<br>" . $sql);
		$now = getdate();
		$now = $now[0];
		/*fd
		$max = session_get_cookie_params();
		$max = $max["lifetime"];
		*/
		$max = $COOKIE_LIFETIME;
		if (mysql_num_rows ($result) > 0) {
			// if now - SessionTime in db is greater than cookie lifetime, then the user has timed out
			$row = mysql_fetch_array($result);
			if (($now - strtotime ($row["SessionTime"])) > $max) {
				echo "SESSION EXPIRED";
				$sql ="DELETE FROM UserSessions WHERE UserID='" . $_SESSION["UserID"] . "'";
				mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>" . $sql);

				$_SESSION["ValidLogin"] = 0;
				unset ($_SESSION["user"]);
				$_SESSION["UserID"] = "";
				$_SESSION["PrevVisit"] = date ("Y-m-d H:i:s");
				$_SESSION["UserTypeID"] = -1;
				$_SESSION["UserTypeName"] = "";
			}
			else {
				$_SESSION["ValidLogin"] = 1;
				$user = new User (
					$row["UserID"],
					$row["DisplayName"],
					$row["UserSignature"],
					date ("Y-m-d H:i:s", strtotime ($row["PrevVisit"])),
					$row["UserTypeID"],
					$row["UserTypeName"],
					$row["CoolPoints"],
					$row["TotalTime"]
				);
				$_SESSION["user"] = $user;
				$_SESSION["UserID"] = $row["UserID"];
				$_SESSION["PrevVisit"] = date ("Y-m-d H:i:s", strtotime ($row["PrevVisit"]));
				$_SESSION["UserTypeID"] = $row["UserTypeID"];
				$_SESSION["UserTypeName"] = $row["UserTypeName"];
			}
		}
		else
			$_SESSION["ValidLogin"] = 0;

		mysql_free_result($result);

		// clean up old sessions....
		$rand = mt_rand(0, 100);
		if ($rand > 50) {
			mysql_query ("DELETE FROM UserSessions WHERE UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(SessionTime) > " . (15 * 60));
		}

		//echo "MAX WASTER: " . Application::getInstance()->getTopTimeWaster();
		//print_r (Application::getInstance());
		return ("");
	}
	//--------------------------------------------------------------------
	// Delete invalid session records from the database
	//--------------------------------------------------------------------
	function session_gc ($maxlifetime) {
		//echo "session_gc()";
		$db = mysql_connect($GLOBALS["db_server"], $GLOBALS["db_uid"], $GLOBALS["db_pwd"]) or die("Could not connect : " . mysql_error());
		mysql_select_db($GLOBALS["db_name"]) or die("Could not select database");
		$now = getdate();
		$now = $now[0];
		//$now = date ("Y-m-d H:i:s");
		$max = session_get_cookie_params();
		$max = $max["lifetime"];
		if ($max != 0) {
			$sql = "DELETE FROM UserSessions WHERE " . $now . " - UNIX_TIMESTAMP(SessionTime) > " . $max;
			//echo $sql;
			mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>" . $sql);
		}

		return (true);
	}
	//--------------------------------------------------------------------
	// Update or save the session state by updating (or inserting) the
	// record for $_SESSION["UserID"]
	//--------------------------------------------------------------------
	function write_session ($key, $val) {
		//print_r ($_SESSION);
		if ($_SESSION["ValidLogin"] == 1 && $_SESSION["UserID"] != "") {
			$db = mysql_connect($GLOBALS["db_server"], $GLOBALS["db_uid"], $GLOBALS["db_pwd"]) or die("Could not connect : " . mysql_error());
			mysql_select_db($GLOBALS["db_name"]) or die("Could not select database");
			$sql = "SELECT * FROM UserSessions WHERE UserID='" . $_SESSION["UserID"] . "'";
			$result = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>" . $sql);
			if (mysql_num_rows ($result) > 0) {
				$sql = "UPDATE UserSessions SET SessionID='" . $key . "', SessionTime='" . date ("Y-m-d H:i:s") . "' WHERE UserID='" .
					$_SESSION["UserID"] . "'";
				//echo $sql;
				if (mysql_query ($sql) != 1) {
					print "Error while writing session state to database." . mysql_error () . "<br>" . $sql;
				}
			}
			else {
				$sql = "INSERT INTO UserSessions (UserID, SessionTime, SessionStart, SessionID) VALUES ('" . $_SESSION["UserID"] .
					"','" . date ("Y-m-d H:i:s") . "','" . date ("Y-m-d H:i:s") . "','" . $key . "')";
				//echo $sql;
				if (mysql_query ($sql) != 1) {
					print "Error while writing session state to database." . mysql_error () . "<br>" . $sql;
				}
			}
			mysql_free_result ($result);
		}
		return (true);
	}
	function close_session () {
		//echo "<br>close_session<br>";
		return (true);
	}
	function destroy_session ($key) {
		$_SESSION = array ();
		return (true);
	}


	/**
	 * Gets a file extension for an image
	 */
	function getImageExtension ($mime) {
		if ($mime == "image/pjpeg")
			return "jpg";
		else if ($mime == "image/gif")
			return "gif";
		else if ($mime == "image/jpeg")
			return "jpg";
		else if ($mime == "image/bitmap")
			return "bmp";
		else
			return $mime;
	}

	/**
	 * Highlights target words in the specified string
	 */
	function highlightText ($src, $target, $substring) {
		$maxchars = 150;
		if ($substring && strlen($src) > $maxchars) {
			return "..." . str_replace ($target, "<span class=\"highlightedText\">" . $target . "</span>", substr($src, strpos($src, $target)-50, 150)) . "...";
		}
		else
			return str_replace ($target, "<span class=\"highlightedText\">" . $target . "</span>", $src);
	}

	/****************************************************************
	 *
	 * Properties functions
	 *
	 ****************************************************************/
	function getTempDir () {
		return getProperty("temp.dir", $TEMP_DIR);
	}
	function getUploadsDir () {
		return getProperty("uploads.dir", $UPLOADS_DIR);
	}
	function getJournalItemsDir () {
		return "/uploads/journalentries";
	}
	function getJournalItemsUploadDir () {
		return getUploadsDir() . "/journalentries";
	}
	function getListItemsDir () {
		return "/uploads/listitems";
	}
	function getListItemsUploadDir () {
		return getUploadsDir() . "/listitems";
	}
	function getProperty ($propKey, $default) {
		return is_null(Properties::getInstance()->getProperty($propKey)) ? $default : Properties::getInstance()->getProperty($propKey);
	}

	//-------------------------------------------------------------------------
	// Date and time functions
	//-------------------------------------------------------------------------
	function dateSub ($timestamp, $seconds,$minutes,$hours,$days,$months,$years) {
	   $mytime = mktime(1+$hours,0+$minutes,0+$seconds,1+$months,1+$days,1970+$years);
	   return $timestamp - $mytime;
	}
	function dateAdd($timestamp, $seconds,$minutes,$hours,$days,$months,$years) {
	   $mytime = mktime(1+$hours,0+$minutes,0+$seconds,1+$months,1+$days,1970+$years);
	   return $timestamp + $mytime;
	}
	function dayOfWeek($timestamp) {
	   return intval(strftime("%w",$timestamp));
	}
	function daysInMonth($timestamp) {
	   $timepieces    = getdate($timestamp);
	   $thisYear          = $timepieces["year"];
	   $thisMonth        = $timepieces["mon"];
	   for($thisDay=1;checkdate($thisMonth,$thisDay,$thisYear);$thisDay++);
	   return $thisDay;
	}
	function firstDayOfMonth($timestamp) {
	   $timepieces = getdate($timestamp);
	   return mktime($timepieces["hours"],
	   				$timepieces["minutes"],
					$timepieces["seconds"],
					$timepieces["mon"],
					1, $timepieces["year"]);
	}
	function monthStartWeekDay($timestamp) {
	   return dayOfWeek(firstDayOfMonth($timestamp));
	}
	function weekDayString($weekday) {
		$myArray = Array(0 => "Sun",
				1 => "Mon", 2 => "Tue",
				3 => "Wed", 4 => "Thu",
				5 => "Fri", 6 => "Sat");
		return $myArray[$weekday];
	}
	function stripTime($timestamp) {
		$timepieces        = getdate($timestamp);
		return mktime(0, 0, 0, $timepieces["mon"],
						$timepieces["mday"],
						$timepieces["year"]);
	}
	function getDayOfYear($timestamp) {
		$timepieces = getdate($timestamp);
		return intval($timepieces["yday"]);
	}
	function getYear($timestamp) {
		$timepieces        = getdate($timestamp);
		return intval($timepieces["year"]);
	}
	function dayDiff($timestamp1,$timestamp2) {
		$dayInYear1 = getDayOfYear($timestamp1);
		$dayInYear2 = getDayOfYear($timestamp2);
		return ((getYear($dayInYear1)*365 + $dayInYear1) - (getYear($dayInYear2)*365 + $dayInYear2));
	}

	$db = mysql_connect(
		$props->getProperty("db.server"),
		$props->getProperty("db.username"),
		$props->getProperty("db.password")
	) or die("Could not connect : " . mysql_error());
	mysql_select_db($props->getProperty("db.name")) or die(mysql_error());

	//-------------------------------------------------------------------------
	// Traps "paction" form variable
	//-------------------------------------------------------------------------
	if (isset ($_POST["paction"]))
		$page_action = $_POST["paction"];
	else if (isset ($_GET["paction"]))
		$page_action = $_GET["paction"];
	else
		$page_action = "";
?>
