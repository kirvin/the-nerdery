<?php
require_once ("../includes/nerdery/net.theirvins.util.MySQLUtil.php");

$giveOrTake = $_POST["plus_minus"];
if ($giveOrTake != "add" && $giveOrTake != "subtract")
	$giveOrTake = $_GET["plus_minus"];
$numberOfPoints = $_POST["number_points"];
if ($numberOfPoints <= 0)
	$numberOfPoints = $_GET["number_points"];
$userID = $_POST["userID"];
if (strlen($userID) < 1)
	$userID = $_GET["userID"];
$reasonFor = $_POST["reasonFor"];
if (strlen($reasonFor) < 1)
	$reasonFor = $_GET["reasonFor"];
$given_by = $_POST["given_by"];
if (strlen($given_by) < 1)
	$given_by = $_GET["given_by"];
$missingParams = "";

if ($giveOrTake != "add" && $giveOrTake != "subtract")
	$missingParams = $missingParams . "plus_minus,";
if ($numberOfPoints <= 0)
	$missingParams = $missingParams . "number_points,";
if (strlen($userID) < 1)
	$missingParams = $missingParams . "userID,";
if (strlen($reasonFor) < 1)
	$missingParams = $missingParams . "reasonFor,";
if (strlen($given_by) < 1)
	$missingParams = $missingParams . "given_by,";

if (strlen($missingParams) > 0)
	echo "The following parameters are missing or invalid in this request:  " . $missingParams;
else {
	$sql_string = "INSERT INTO UserCoolPoints (UserID, CoolPoints, AddPoints, Reason, ActionDate, GivenBy) VALUES ('" .
		$userID . "', " . $numberOfPoints . ", " . (($giveOrTake == "add") ? 1 : 0) . ", '" .
		$reasonFor . "', '" . date_format(date_create(), MySQLUtil::$MYSQL_DATE_FORMAT) .
		"', '" . $given_by . "')";
	//echo $sql_string;
	MySQLUtil::getConnection();
	mysql_query($sql_string) or die ("Unable to execute query '" . $sql_string . "'");
	$sql_string = "UPDATE Users SET CoolPoints=((SELECT case when sum(CoolPoints) is null then 0 else sum(CoolPoints) end FROM UserCoolPoints WHERE AddPoints=1 AND UserID='" . $userID . "') - (SELECT case when sum(CoolPoints) is null then 0 else sum(CoolPoints) end FROM UserCoolPoints WHERE AddPoints=0 AND UserID='" . $userID . "')) WHERE UserID='" . $userID . "'";
	mysql_query($sql_string) or die ("Unable to execute query '" . $sql_string . "'");

	$sql_string = "INSERT INTO NerderyEvents (EventTitle, EventDescription, UserID, EventTypeID, EventURL) VALUES (CONCAT((SELECT DisplayName FROM Users WHERE UserID='" . $userID . "'), ' became cooler'), CONCAT((SELECT DisplayName FROM Users WHERE UserID='" . $given_by . "'), ' gave ', (SELECT DisplayName FROM Users WHERE UserID='" . $userID . "'), ' some cool points.'), '" . $_SESSION["UserID"] . "', (SELECT EventTypeID FROM NerderyEventType WHERE EventTypeName='CoolPoints'), '/cool_points.php?userID=" . $userID . "')";
    mysql_query ($sql_string) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql_string);
}

?>
