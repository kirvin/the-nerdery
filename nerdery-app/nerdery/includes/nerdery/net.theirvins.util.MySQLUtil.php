<?php
require_once("net.theirvins.util.Properties.php");

class MySQLUtil {

	private static $connected = false;
	public static $MYSQL_DATE_FORMAT = "Y-m-d h:i:s";

	public static function getConnection () {
		if (!MySQLUtil::$connected) {
			/*
			 * get db props and set up doctrine manager
			 */
			$props = Properties::getInstance();
			$props->loadFromFile();
			$db_server = $props->getProperty("db.server");
			$db_user = $props->getProperty("db.username");
			$db_password = $props->getProperty("db.password");
			$db_name = $props->getProperty("db.name");
			$dsn = 'mysql://' . $db_user . ':' . $db_password . '@' . $db_server . '/' . $db_name;
			$db = mysql_connect($db_server, $db_user, $db_password);
			if (mysql_select_db($db_name)) {
				MySQLUtil::$connected = true;
			}
		}
		
	}


}


?>