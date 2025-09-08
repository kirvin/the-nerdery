<?php
/**
 * Class definition for site user object
 */
class User {

	var $userID = "";
	var $displayName = "";
	var $signatureFile = 0;
	var $previousVisit = "";
	var $userType = -1;
	var $userTypeName = "";
	var $coolPoints = 0;
	var $totalTime = 0;

	function User ($uid, $dn, $sf, $pv, $t, $tn, $cp, $tt) {
		$this->userID = $uid;
		$this->displayName = $dn;
		$this->signatureFile = $sf;
		$this->previousVisit = $pv;
		$this->userType = $t;
		$this->userTypeName = $tn;
		$this->coolPoints = $cp;
		$this->totalTime = $tt;
	}

	function save () {
		$sql = "UPDATE Users SET DisplayName='$this->displayName', UserSignature=$this->signatureFile, " .
			"PrevVisit='$this->previousVisit', UserTypeID=$this->userType, CoolPoints=$this->coolPoints, " .
			"TotalTime=$this->totalTime WHERE UserID='$this->userID'";
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
	}

}
