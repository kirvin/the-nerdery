<?php
	ob_start ();
	require ("./includes/global_functions.php");
	require ("./includes/framework_functions.php");
	//require ("./includes/form_functions.php");
	session_set_save_handler ("open_session", "close_session", "read_session", "write_session",
		"destroy_session", "session_gc");
	session_start ();

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
	else if ($_GET["l"] == "true") {
		$sql = "SELECT S.*, U.* FROM Users AS U INNER JOIN UserSessions AS S ON U.UserID=S.UserID " .
			"WHERE S.UserID='" . $_SESSION["UserID"] . "'";
		//echo $sql . "<br>";
		$rs = mysql_query ($sql) or die ("ERROR: " . mysql_query() . "<br>SQL: " . $sql);
		if (mysql_num_rows ($rs) > 0) {
			//echo mysql_affected_rows();
			$row = mysql_fetch_array ($rs);
			//print_r ($row);
			//echo $row["SessionTime"] . "::" . $row["SessionStart"] . "::" . strtotime ($row["SessionTime"]) . 
			//	"::" . strtotime ($row["SessionStart"]);
			$tt = ceil ((strtotime ($row["SessionTime"]) - strtotime ($row["SessionStart"])) / 60);
			//echo "TotalTime increased by " . $tt;
			//add_date
			$_SESSION["user"]->previousVisit = date ("Y-m-d H:i:s");
			$_SESSION["user"]->totalTime += $tt;
			$_SESSION["user"]->save();
			//$sql = "UPDATE Users SET PrevVisit='" . date ("Y-m-d H:i:s") . "', TotalTime = " .
			//	"(TotalTime + $tt) WHERE UserID='" . $_SESSION["UserID"] . "'";
			//mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL::" . $sql);
			//echo mysql_affected_rows();
			$sql = "DELETE FROM UserSessions WHERE UserID='" . $_SESSION["UserID"] . "'";
			mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>" . $sql);
			unset ($_SESSION["ValidLogin"]);
			unset ($_SESSION["user"]);
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
?>
