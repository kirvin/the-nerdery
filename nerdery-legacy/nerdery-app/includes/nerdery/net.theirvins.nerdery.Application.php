<?php
require_once ("net.theirvins.util.MySQLUtil.php");

/**
 * Represents the state of the web application
 *
 */
class Application {

	private static $instance;
	private $properties = array();
	private $isDirty = false;


	/**
	 * 
	 *
	 */
	private function __construct () {
		$conn = MySQLUtil::getConnection();
		$rs = mysql_query("SELECT * FROM ApplicationState") or die ("Error while initializing Application: " . mysql_error());
		if (mysql_affected_rows() > 0) {
			$row = mysql_fetch_assoc($rs);
			while ($row != false) {
				//echo "add property {" . $row["AttributeKey"] . "," . $row["AttributeValue"] . "}";
				$this->properties[$row["AttributeKey"]] = $row["AttributeValue"];
				$row = mysql_fetch_assoc($rs);
			}
		}

		/*
		 * Set any required properties that were not retrieved from the database
		 */
		$temp = $this->properties["TopTimeWaster"];
		if (strlen($temp) <= 0) {
			$rs = mysql_query("SELECT UserID FROM Users WHERE TotalTime = (SELECT MAX(TotalTime) FROM Users) LIMIT 1") or die ("SQL ERROR: " . mysql_error());
			$vals = mysql_fetch_array ($rs);
			$this->setTopTimeWaster($vals["UserID"]);
		}
		$temp = $this->properties["TopCoolPoints"];
		if (strlen($temp) <= 0) {
			$rs = mysql_query("SELECT UserID FROM Users WHERE CoolPoints = (SELECT MAX(CoolPoints) FROM Users) LIMIT 1") or die ("SQL ERROR: " . mysql_error());
			$vals = mysql_fetch_array ($rs);
			$this->setCoolestUser ($vals["UserID"]);
		}
	}

	/**
	 * 
	 *
	 */
	public static function getInstance () {
		if (is_null(self::$instance))
			self::$instance = new self();
		return self::$instance;
	}

	/**
	 * 
	 */
	public function setTopTimeWaster ($ttw) {
		$currVal = $this->properties["TopTimeWaster"];
		if ($ttw != $currVal) {
			$this->isDirty = true;
			$this->properties["TopTimeWaster"] = $ttw;
		}
	}

	/**
	 * Gets the person with the most time spent on the nerdery
	 *
	 * @return unknown
	 */
	public function getTopTimeWaster () {
		return $this->properties["TopTimeWaster"];
	}

	/**
	 * Sets the user with the most cool points.  If the specified user is different
	 * than the current value, then calling Application->save() will persist the 
	 * value to the database.
	 *
	 * @param unknown_type $tcu
	 */
	public function setCoolestUser ($tcu) {
		$currVal = $this->properties["TopCoolPoints"];
		if ($tcu != $currVal) {
			$this->isDirty = true;
			$this->properties["TopCoolPoints"] = $tcu;
		}
	}

	/**
	 * Gets the user ID of the user with the most cool points.
	 *
	 * @return unknown
	 */
	public function getCoolestUser () {
		return $this->properties["TopCoolPoints"];
	}

	/**
	 * Saves the current state of the Application
	 *
	 */
	public function save () {
		if ($this->isDirty) {
			//echo "Save application state...";
			$sql = "DELETE FROM ApplicationState";
			mysql_query ($sql) or die ("SQL ERROR: " . mysql_error() . " -> " . $sql);
			$keys = array_keys($this->properties);
			foreach ($keys as $key) {
				$sql = "INSERT INTO ApplicationState (AttributeKey, AttributeValue) VALUES ('" . $key . "','" . $this->properties[$key] . "')";
				mysql_query ($sql) or die ("SQL ERROR: " . mysql_error() . " -> " . $sql);
			}
		}
	}
}

?>