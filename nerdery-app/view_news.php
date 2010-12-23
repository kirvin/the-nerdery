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

require_once ('includes/news_functions.php');
require_once ('includes/class.dropshadow.php');

	$journal_id = -1;
	if ($page_action == "insertNewsColumn") {
		$sql = "INSERT INTO Journals (JournalTitle, JournalOwner, JournalDescription, CreatedDate, LastModified) " .
			"VALUES ('" . $_POST["column_title"] . "','" . $_SESSION["UserID"] . "','" . 
			$_POST["column_description"] . "','" . date ("Y-m-d H:i:s a") . "','" . date ("Y-m-d H:i:s a") . "')";
		mysql_query ($sql) or die ("ERROR: " . mysql_error () . "<br>SQL: " . $sql);
		$journal_id = mysql_insert_id();
	}


	if (isset($_GET["j"]))
		$journal_id = $_GET["j"];
	else if (isset($_POST["j"]))
		$journal_id = $_POST["j"];

	if ($journal_id == -1)
		header ("location: news.php");

	if ($page_action == "insertNewsItem") {
		if (strlen ($_FILES["item_file"]["name"]) > 0) {
			$td = $TEMP_DIR;
			$ud = $JOURNALENTRIES_DIR . $journal_id . "/";
			// get file extension if possible
			$fe = "";
			if ($_FILES["item_file"]["type"] != "") {
				if ($_FILES["item_file"]["type"] == "image/pjpeg" || $_FILES["item_file"]["type"] == "image/jpeg")
					$fe = "jpg";
				else if ($_FILES["item_file"]["type"] == "image/gif")
					$fe = "gif";
				else if ($_FILES["item_file"]["type"] == "image/bmp")
					$fe = "bmp";
			}
			else {
				$tokens = explode (".", $_FILES["item_file"]["name"]);
				if (count($tokens) > 1) {
					$fe = $tokens[count($tokens)-1];
				}
			}
		}
		$sql = "INSERT INTO JournalEntries (JournalID, JournalEntryDate, LastModified, JournalEntrySubject, JournalEntryText, FileExtension, JournalEntryAuthor) " .
			"VALUES (" . $journal_id . ",'" . date ("Y-m-d H:i:s") . "','" . date ("Y-m-d H:i:s") . "','" . $_POST["item_title"] . "','" . 
			$_POST["item_description"] . "','" . $fe . "','" . $_SESSION["UserID"] . "')";
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
		$nid = mysql_insert_id();
		$sql = "UPDATE Journals SET LastModified='" . date ("Y-m-d H:i:s a") . "' WHERE JournalID=" . $journal_id;
		mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
		// finish file upload
		if (strlen ($_FILES["item_file"]["name"]) > 0) {
			if (move_uploaded_file ($_FILES["item_file"]["tmp_name"], $td . $_FILES["item_file"]["name"])) {
				$new_file = $ud . $nid;
				if (!file_exists ($ud))
					mkdir ($ud);
				$new_file = $new_file . "." . $fe;
				$ds = new dropShadow (FALSE);
				$ds->loadImage ($td . $_FILES["item_file"]["name"]);
				$ds->resizeToSize ($IMAGE_WIDTH, 0);
				$ds->saveFinal ($new_file);
				unlink ($td . $_FILES["item_file"]["name"]);
			}
		}
	}

	WriteHeader (5, "The Nerdery::News"); 
	writeCP ();
	
	$sql = "SELECT * FROM Journals WHERE JournalID=$journal_id";
	$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql . "<br>");
	$row = mysql_fetch_array ($rs);
?>

<table border="0" width="750" cellpadding="0" cellspacing="0">
	<tr>
		<td width="25" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<span class="boldWhiteMediumText">
				<a href="news.php" class="boldWhiteLink">News Columns</a>
				&nbsp;&gt;&gt;&nbsp;
				Add to <?php echo $row["JournalTitle"] ?>
			</span>
		</td>
		<td width="25"><img border="0" height="32" width="25" src="images/section.header.e.gif"></td>
	</tr>
	<tr>
		<td align="center" colspan="3">
			<?php
				$fstruct = array (
					array ("paction", "", "hidden", "insertNewsItem", false),
					array ("j", "", "hidden", $journal_id, false),
					array ("item_title", "Title", "text", "", true),
					array ("item_file", "File", "file", "", true),
					array ("item_description", "Description", "textarea", "", true),
				);
				createForm ($fstruct, "field", "addList", "view_news.php", true, "Add News", "");
			?>
		</td>
	</tr>
	<tr><td colspan="3"><br></td></tr>
	<tr>
		<td width="25" background="images/section.header.bg.gif"><img height="32" width="25" src="images/section.header.w.gif"></td>
		<td width="700" background="images/section.header.bg.gif">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td>
						<span class="boldWhiteMediumText">
							<a href="news.php" class="boldWhiteLink">News Columns</a>
							&nbsp;&gt;&gt;&nbsp;
							<?php echo $row["JournalTitle"] ?>
						</span>
					</td>
					<td align="right">
					</td>
				</tr>
			</table>
		</td>
		<td width="25"><img border="0" height="32" width="25" src="images/section.header.e.gif"></td>
	</tr>
	<tr>
		<td></td>
		<td align="center">
		<?php
			writeNewsColumn ($journal_id);
		?>
		</td>
		<td></td>
	</tr>
</table>


<?php
	WriteFooter ();

	$application->save();
?>