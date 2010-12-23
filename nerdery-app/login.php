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

$err_msg = -1;

	if ($page_action == "verifyUser") {
		/* Perform SQL query */
		$query = "SELECT UserID FROM Users WHERE UserID='" . $_POST["user_id"] . "'";
		//echo $query;
		$result = mysql_query($query) or die("Query failed : " . mysql_error());
		if (mysql_num_rows ($result) == 1) {
			$query = "SELECT U.*, UT.UserTypeName FROM Users AS U, UserTypes AS UT WHERE UserID='" . $_POST["user_id"] . "' AND UserPassword='" . $_POST["user_pwd"] . "' AND U.UserTypeID=UT.UserTypeID";
			//print "[process_login] " . $query . "<br>";
			$result = mysql_query($query) or die ("Query failed: " . mysql_error());
			if (mysql_num_rows ($result) == 1) {
				$row = mysql_fetch_array($result);
				$user = new User (
					$_POST["user_id"],
					$row["DisplayName"],
					$row["UserSignature"],
					date ("Y-m-d H:i:s", strtotime ($row["PrevVisit"])),
					$row["UserTypeID"],
					$row["UserTypeName"],
					$row["CoolPoints"],
					$row["TotalTime"]
				);
				$_SESSION["UserID"] = $_POST["user_id"];
				$_SESSION["SessionTime"] = date ("Y-m-d H:i:s");
				$_SESSION["ValidLogin"] = 1;
				$_SESSION["PrevVisit"] = date ("Y-m-d H:i:s", strtotime ($row["PrevVisit"]));
				$_SESSION["UserTypeID"] = $row["UserTypeID"];
				$_SESSION["UserTypeName"] = $row["UserTypeName"];
				$_SESSION["SessionStart"] = date ("Y-m-d H:i:s");
				if (isset ($_POST["r"]) && $_POST["r"] != "")
					//echo ("go to: location: " . $_POST["r"]);
					header ("location: " . $_POST["r"]);
				else if (isset ($_GET["r"]) && $_GET["r"] != "") {
					//echo ("go to: location: " . $_GET["r"]);
					header ("location: " . $_GET["r"]);
					//print_r ($_SESSION);
				}
				else
					//echo "go to: location: index.php";
					header ("location: index.php");
			}
			else
				$err_msg = 2;
		}
		else
			$err_msg = 1;
		mysql_free_result($result);
	}
	/*
	 * Handle a logout request
	 */
	else if ($_GET["l"] == "true") {
		$sql = "SELECT S.*, U.* FROM Users AS U INNER JOIN UserSessions AS S ON U.UserID=S.UserID " .
			"WHERE S.UserID='" . $_SESSION["UserID"] . "'";
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_query() . "<br>SQL: " . $sql);
		/*
		 * if this user has a record in the UserSessions table...
		 */
		if (mysql_num_rows ($rs) > 0) {
			$row = mysql_fetch_array ($rs);
			$tt = ceil ((strtotime ($row["SessionTime"]) - strtotime ($row["SessionStart"])) / 60);
			$_SESSION["user"]->previousVisit = date ("Y-m-d H:i:s");
			$_SESSION["user"]->totalTime += $tt;
			$_SESSION["user"]->save();
			$sql = "DELETE FROM UserSessions WHERE UserID='" . $_SESSION["UserID"] . "'";
			mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>" . $sql);
			unset ($_SESSION["ValidLogin"]);
			unset ($_SESSION["user"]);
			$sql = "SELECT UserID FROM Users WHERE TotalTime=(SELECT MAX(TotalTime) FROM Users)";
			$rs = mysql_query($sql) or die ("ERROR: " . mysql_error() . "<br>" . $sql);
			$results = mysql_fetch_array($rs);
			$max_waster = $results["UserID"];
			//echo "max_waster: '" . $max_waster . "'";
			Application::getInstance()->setTopTimeWaster($max_waster);
			Application::getInstance()->save();
		}
	}
 
	WriteHeader (1, "The Nerdery User Login");
?>

<br><br><br><br>

<?php
	if ($err_msg == 1) {
		echo "The user name '" . $_POST["user_id"] . "' was not found in our records.  Please try again.";
	}
	else if ($err_msg == 2) {
		echo "Incorrect password for user '" . $_POST["user_id"] . "'.  Please try again.";
	}

	$frm = new Form ("field", "loginForm", "login.php?r=" . $_GET["r"], "verifyUser");
	$frm->addFormElement ("user_id", "text", "User ID", "", "yes");
	$frm->addFormElement ("user_pwd", "password", "Password", "", "yes");
	$frm->draw ();
?>
<br><br><br><br><br><br><br><br><br><br><br><br>

<?php
	WriteFooter ();

	$application->save();
?>