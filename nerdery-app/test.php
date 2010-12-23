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

include_once("includes/nerdery/net.theirvins.util.Properties.php");
include_once("includes/nerdery/net.theirvins.nerdery.domain.NerderyList.php");
require_once("includes/doctrine/Doctrine.php");
spl_autoload_register(array('Doctrine', 'autoload'));

/*
 * get db props and set up doctrine manager
 */
$props = new Properties ();
$props->loadFromFile("nerdery.properties");
$db_server = $props->getProperty("db.server");
$db_user = $props->getProperty("db.username");
$db_password = $props->getProperty("db.password");
$db_name = $props->getProperty("db.name");
$dsn = 'mysql://' . $db_user . ':' . $db_password . '@' . $db_server . '/' . $db_name;
$conn = Doctrine_Manager::connection($dsn);

/*
 * get a connection to the db
 */
//$conn = new PDO($dsn, $user, $password);

$list = new NerderyList ();
$list->title = "Test List " . date_format(date_create(), MySQLUtil::$MYSQL_DATE_FORMAT);
$list->createdDate = date_format(date_create(), MySQLUtil::$MYSQL_DATE_FORMAT);
$list->lastModified = date_format(date_create(), MySQLUtil::$MYSQL_DATE_FORMAT);
$list->owner = 'kirvin';
$list->description = 'created by test.php @ ' . date_format (date_create(), MySQLUtil::$MYSQL_DATE_FORMAT);

printf ("ID before: " . $list->id);
$list->save($conn);
printf ("ID after: " . $list->id);



?>