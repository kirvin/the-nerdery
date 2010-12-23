<?php
require_once ("includes/nerdery/net.theirvins.nerdery.Application.php");
require_once ("includes/global_functions.php");
require_once ('includes/cp.php');
require_once ("includes/framework_functions.php");
require_once ('includes/form_functions.php');
require_once ('includes/magpie/rss_fetch.inc');

define('MAGPIE_CACHE_AGE', 60);

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

WriteHeader (1, "Nerdery Home", "Since you were last here...");
writeCP ();
?>
<style type="text/css">
	#homePageSection {
		width:	100%;
		font-weight: bold;
		border-style: solid;
		border-width: 2px;
		border-top:	none;
		border-left:none;
		border-right:none;
		border-color: #89A798;
	}

	#newsItem {
		list-style-type: none;
		padding-left: 20px;
		padding-bottom: 10px;
	}
	
	span.newsItemDate {
		font-weight: bold;
		font-size: 10pt;
		color:	#333399;
	}

	span.newsItemTitle {
		font-size: 10pt;
	}

	span.newsItemDescription {
		font-size: 10pt;
		font-style: italic;
		margin-left:  15px;
	}
</style>

<table border="0" width="750" cellpadding="0" cellspacing="0">
	<tr>
		<td colspan="3">
		<br />
		<?php
			echo "<div id=\"homePageSection\">You were last here on " . date ("D, M d, Y  h:m:s", strtotime ($_SESSION["PrevVisit"])) . "</div>";

			if ($application->getTopTimeWaster() == $_SESSION["UserID"]) {
				echo "<br>Congratulations on wasting more time than anyone on the Nerdery!<br><br><br>";
			}
		?>
			<div id="homePageSection">Recently on The Nerdery...</div>
		<?php
			//echo "LAST: " . $_SESSION["PrevVisit"];
			$count = 0;
			$rss = fetch_rss("http://nerdery.theirvins.net/nerdery_rss.php");
			foreach ($rss->items as $item) {
				if ($count < 10 || strtotime($item["pubdate"]) >= strtotime($_SESSION["PrevVisit"])) {
					echo "<li id=\"newsItem\"><span class=\"newsItemDate\">" . date("D, m/d/Y h:i", strtotime($item["pubdate"])) . 
						"</span>&nbsp;-&nbsp;<span class=\"newsItemTitle\">" . $item["description"] . "</span>";// .
						//"<br/><span class=\"newsItemDescription\">" . $item["description"] . "</span></li>";
				}
				$count++;
			}
		?>

		</td>
	</tr>
</table>

<?php
	WriteFooter ();

	$application->save();
?>