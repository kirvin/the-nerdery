<?php
	ob_start ();

	require ("includes/framework_functions.php");
	require ("includes/global_functions.php");
	require ("includes/class.dropshadow.php");
	//require ("includes/form_functions.php");

	session_set_save_handler ("open_session", "close_session", "read_session", "write_session",
		"destroy_session", "session_gc");
	session_start ();

	if ($_SESSION["ValidLogin"] != 1)
		header ("location: login.php?r=" . $_SERVER["PHP_SELF"]);
?>

<?php
	WriteHeader (1, "Nerdery Home", "Edit your information");

	if ($page_action == "updateUser") {
		$tmp = realpath (".") . "/temp";
		if (!file_exists ($tmp)) {
			mkdir ($tmp);
		}
		$tmpfile = $tmp . "/" . $_FILES["signature_file"]["name"];
		if (move_uploaded_file ($_FILES["signature_file"]["tmp_name"], $tmpfile)) {
			$ext = getImageExtension ($_FILES["signature_file"]["type"]);
			//echo $ext;
			if ($ext == "gif") {
				echo "The Nerdery does not currently support .gif files for signatures.  This will (hopefully) be fixed soon...";
			}
			else {
				$base = realpath (".") . "/images/signatures/" . $_SESSION["user"]->userID . ".";
				$final = $base . $ext;
				$resizer = new dropShadow (true);
				$resizer->loadImage ($tmpfile);
				$resizer->resizeToSize ($SIGNATURE_WIDTH, 0);
				$resizer->saveFinal ($final);
				copy ($final, $base . "sig");
				unlink ($final);
				$_SESSION["user"]->signatureFile = 1;
			}
		}
		$_SESSION["user"]->displayName = $_POST["display_name"];
		$_SESSION["user"]->save();
	
	}
?>


<br>
<table border="0" width="750" cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<?php
				//print_r ($_SESSION["user"]);
				//echo $_SESSION["user"]->userID;
				if ($_SESSION["user"]->signatureFile == 1) {
					$sig_file = "/images/signatures/" . $_SESSION["user"]->userID . ".sig";
				}
				else
					$sig_file = "";

				$frm = new Form ("field", "editUser", "edit_user.php", "updateUser");
				$frm->addFormElement ("display_name", "text", "Display Name", $_SESSION["user"]->displayName, true);
				$frm->addFormElement ("signature_file", "file", "Signature File", "", false);
				$frm->addFormElement ("signature_image", "image", "Current Signature", $sig_file, false);
				$frm->draw();
			?>
		</td>
	</tr>
</table>

<?php
	WriteFooter ();
?>

